<?php
class ContributionImportUsers extends Omeka_Job_AbstractJob
{
    public function perform()
    {
        $db = get_db();
        //import the contributors to Guest Users
        $sql = "SELECT * FROM $db->ContributionContributors";
        $res = $db->query($sql);
        $data = $res->fetchAll();
        //key: contributor id; value user id
        $contributorUserMap = array();
        $usernameFiller = 11111; //count up for non-unique usernames, and ones too short
        $emailFiller = 1;
        $validatorOptions = array(
                'table'   => $db->getTable('User')->getTableName(),
                'field'   => 'username',
                'adapter' => $db->getAdapter()
        );
        $uniqueUsernameValidator = new Zend_Validate_Db_NoRecordExists($validatorOptions);
        $emailValidator = new Zend_Validate_EmailAddress();
        foreach($data as $contributor) {
            if($user = $db->getTable('User')->findByEmail($contributor['email'])) {
                $userContributorMap[$user->id][] = $contributor['id'];
            } else {
                $user = new User();
                //create username from email
                $usernameParts = explode('@', $contributor['email']);
                $username = preg_replace("/[^a-zA-Z0-9\s]/", "", $usernameParts[0]);
        
                if( (strlen($username) < $user::USERNAME_MIN_LENGTH) || !$uniqueUsernameValidator->isValid($username)) {
                    $username = $username . $usernameFiller;
                    $usernameFiller++;
                }
                $email = $contributor['email'];
                if(!$emailValidator->isValid($email)) {
                    //can't save as a new user w/o valid unique email, so either create
                    //a fake one, or shove all invalid-email contributors onto one real user (superuser)
                    $email = "fake$emailFiller@example.com";
                    $emailFiller++;
                }
                $name = trim($contributor['name']);
                $user->username = $username;
                $user->name = empty($name) ? "user" : $name;
                $user->email = $email;
                $user->role = "guest";
                $user->active = true;
        
                try {
                    $user->save();
                } catch (Exception $e) {
                    _log($e->getMessage());
                    $user = $db->getTable('User')->find(1);
                }
                $userContributorMap[$user->id] = array($contributor['id']);
            }
            release_object($user);
        }
        $this->_mapUsersToItems($userContributorMap);
        //we need to keep track of which contributors got mapped to which users
        //so that the UserProfiles import of contributor info can match people up
        $serialized = serialize($userContributorMap);
        $putResult = file_put_contents(CONTRIBUTION_PLUGIN_DIR . '/upgrade_files/user_contributor_map.txt', $serialized);
    }
    
    private function _mapUsersToItems($userContributorMap)
    {
        $db=get_db();        
        foreach($userContributorMap as $userId=>$contributorIds) {
            $contribIds = implode(',' , $contributorIds);
            //dig up the items contributed and set the owner
            $sql = "SELECT `item_id` FROM $db->ContributionContributedItems WHERE `contributor_id` IN ($contribIds) ";
            $res = $db->query($sql);
            $contributedItemIds =  $res->fetchAll();
            $itemTable = $db->getTable('Item');
            $ids = array();
            foreach($contributedItemIds as $row) {
                $ids[] = $row['item_id'];
            }
            $idsString = implode(',', $ids);
            if(!empty($idsString)) {
                $sql = "UPDATE `omeka_items` SET `owner_id`=$userId WHERE `id` IN ($idsString)";
                $res = $db->query($sql);
            }            
        }
    }
}