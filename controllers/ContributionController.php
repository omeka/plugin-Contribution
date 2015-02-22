<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */

/**
 * Controller for contributions themselves.
 */
class Contribution_ContributionController extends Omeka_Controller_AbstractActionController
{
    protected $_captcha;

    /**
     * Index action; simply forwards to contributeAction.
     */
    public function indexAction()
    {
        $this->_forward('contribute');
    }

    public function myContributionsAction()
    {
        $user = current_user();
        $contribItemTable = $this->_helper->db->getTable('ContributionContributedItem');

        $contribItems = array();
        if(!empty($_POST)) {
            foreach($_POST['contribution_public'] as $id=>$value) {
                $contribItem = $contribItemTable->find($id);
                if($value) {
                    $contribItem->public = true;
                } else {
                    $contribItem->makeNotPublic();
                }
                $contribItem->public = $value;
                $contribItem->anonymous = $_POST['contribution_anonymous'][$id];

                if($contribItem->save()) {
                    $this->_helper->flashMessenger( __('Your contributions have been updated.'), 'success');
                } else {
                    $this->_helper->flashMessenger($contribItem->getErrors());
                }

                $contribItems[] = $contribItem;
            }
        } else {
            $contribItems = $contribItemTable->findBy(array('contributor'=>$user->id));
        }
        $this->view->contrib_items = $contribItems;
    }

    /**
     * Action for main contribution form.
     */
    public function contributeAction()
    {
        $this->_captcha = $this->_setupCaptcha();
        $csrf = new Omeka_Form_SessionCsrf;
        $this->view->csrf = $csrf;
        if(!empty($_POST)) {
            if (!$csrf->isValid($_POST)) {
                $this->_helper->_flashMessenger(__('There was an error on the form. Please try again.'), 'error');
                $typeId = null;
                if (isset($_POST['contribution_type']) && ($postedType = $_POST['contribution_type'])) {
                    $typeId = $postedType;
                } else if ($defaultType = get_option('contribution_default_type')) {
                    $typeId = $defaultType;
                }
                $this->_setupContributeSubmit($typeId);
                return;
            }
            if ($this->_processForm($_POST)) {
                $route = $this->getFrontController()->getRouter()->getCurrentRouteName();
                $this->_helper->_redirector->gotoRoute(array('action' => 'thankyou'), $route);
            } else {
                $typeId = null;
                if (isset($_POST['contribution_type']) && ($postedType = $_POST['contribution_type'])) {
                    $typeId = $postedType;
                } else if ($defaultType = get_option('contribution_default_type')) {
                    $typeId = $defaultType;
                }
                if ($this->_captcha) {
                    $this->view->captchaScript = $this->_captcha->render(new Zend_View);
                }
                $this->_setupContributeSubmit($typeId);

                if(isset($this->_profile) && !$this->_profile->exists()) {
                    $this->_helper->flashMessenger($this->_profile->getErrors(), 'error');
                    return;
                }
            }
        } else {
            if($this->_captcha) {
                $this->view->captchaScript = $this->_captcha->render(new Zend_View);
            }
            $defaultType = get_option('contribution_default_type');
            $this->_setupContributeSubmit($defaultType);
        }
    }

    /**
     * Action for AJAX request from contribute form.
     */
    public function typeFormAction()
    {
        $this->_setupContributeSubmit($_POST['contribution_type']);
    }

    /**
     * Displays terms of service for contribution.
     */
    public function termsAction()
    {
    }

    /**
     * Displays a "Thank You" message to users who have contributed an item
     * through the public form.
     */
    public function thankyouAction()
    {
    }

    /**
     * Common tasks whenever displaying submit form for contribution.
     *
     * @param int $typeId ContributionType id
     */
    public function _setupContributeSubmit($typeId)
    {
        // Override default element form display
        $this->view->addHelperPath(CONTRIBUTION_HELPERS_DIR, 'Contribution_View_Helper');
        $item = new Item;
        $this->view->item = $item;

        $type = get_db()->getTable('ContributionType')->find($typeId);
        $this->view->type = $type;

        //setup profile stuff, if needed
        $profileTypeId = get_option('contribution_user_profile_type');
        if(plugin_is_active('UserProfiles') && $profileTypeId) {
            $this->view->addHelperPath(USER_PROFILES_DIR . '/helpers', 'UserProfiles_View_Helper_');
            $profileType = $this->_helper->db->getTable('UserProfilesType')->find($profileTypeId);
            $this->view->profileType = $profileType;

            if($user = current_user()) {
                $profile = $this->_helper->db->getTable('UserProfilesProfile')->findByUserIdAndTypeId($user->id, $profileTypeId);
            }
            if(empty($profile)) {
                $profile = new UserProfilesProfile();
                $profile->type_id = $profileTypeId;
            }
            $this->view->profile = $profile;
        }
    }

    /**
     * Creates the reCAPTCHA object and returns it.
     * 
     * @return Zend_Captcha_Recaptcha|null
     */
    protected function _setupCaptcha()
    {
        if(current_user()) {
            return false;
        } else {
            return Omeka_Captcha::getCaptcha();
        }
    }

    /**
     * Handle the POST for adding an item via the public form.
     *
     * Validate and save the contribution to the database.  Save the ID of the
     * new item to the session.  Redirect to the consent form.
     *
     * If validation fails, render the Contribution form again with errors.
     *
     * @param array $post POST array
     * @return bool
     */
    protected function _processForm($post)
    {
        if (!empty($post)) {

            //for the "Simple" configuration, look for the user if exists by email. Log them in.
            //If not, create the user and log them in.
            $user = current_user();
            $simple = get_option('contribution_simple');

            if(!$user && $simple) {
                $user = $this->_helper->db->getTable('User')->findByEmail($post['contribution_simple_email']);
            }

            // if still not a user, need to create one based on the email address
            if(!$user) {
                $user = $this->_createNewGuestUser($post);
                if($user->hasErrors()) {
                    $errors = $user->getErrors()->get();
                    //since we're creating the user behind the scenes, skip username and name errors
                    unset($errors['name']);
                    unset($errors['username']);
                    foreach($errors as $error) {
                        $this->_helper->flashMessenger($error, 'error');
                    }
                    return false;
                }
            }

            // The final form submit was not pressed.
            if (!isset($post['form-submit'])) {
                return false;
            }
            if (!$this->_validateContribution($post)) {
                return false;
            }

            $contributionTypeId = trim($post['contribution_type']);
            if ($contributionTypeId !== "" && is_numeric($contributionTypeId)) {
                $contributionType = get_db()->getTable('ContributionType')->find($contributionTypeId);
                $itemTypeId = $contributionType->getItemType()->id;
            } else {
            	$this->_helper->flashMessenger(__('You must select a type for your contribution.'), 'error');
                return false;
            }
            $itemMetadata = array('public'       => false,
                                  'featured'     => false,
                                  'item_type_id' => $itemTypeId);

            $collectionId = get_option('contribution_collection_id');
            if (!empty($collectionId) && is_numeric($collectionId)) {
                $itemMetadata['collection_id'] = (int) $collectionId;
            }

            $fileMetadata = $this->_processFileUpload($contributionType);

            // This is a hack to allow the file upload job to succeed
            // even with the synchronous job dispatcher.
            if ($acl = get_acl()) {
                $acl->allow(null, 'Items', 'showNotPublic');
                $acl->allow(null, 'Collections', 'showNotPublic');
            }
            try {
                //in case we're doing Simple, create and save the Item so the owner is set, then update with the data
                $item = new Item();
                $item->setOwner($user);
                $item->save();
                $item = update_item($item, $itemMetadata, array(), $fileMetadata);
            } catch(Omeka_Validator_Exception $e) {
                $this->flashValidatonErrors($e);
                return false;
            } catch (Omeka_File_Ingest_InvalidException $e) {
                // Copying this cruddy hack
                if (strstr($e->getMessage(), "'contributed_file'")) {
                   $this->_helper->flashMessenger("You must upload a file when making a {$contributionType->display_name} contribution.", 'error');
                } else {
                    $this->_helper->flashMessenger($e->getMessage());
                }
                return false;
            } catch (Exception $e) {
                $this->_helper->flashMessenger($e->getMessage());
                return false;
            }
            $this->_addElementTextsToItem($item, $post['Elements']);
            // Allow plugins to deal with the inputs they may have added to the form.
            fire_plugin_hook('contribution_save_form', array('contributionType'=>$contributionType,'record'=>$item, 'post'=>$post));
            $item->save();
            //if not simple and the profile doesn't process, send back false for the error
            $this->_processUserProfile($post, $user);
            $this->_linkItemToContributedItem($item, $contributor, $post);
            $this->_sendEmailNotifications($user, $item);
            return true;
        }
        return false;
    }

    protected function _processUserProfile($post, $user)
    {
        $profileTypeId = get_option('contribution_user_profile_type');
        if($profileTypeId && plugin_is_active('UserProfiles')) {
            $profile = $this->_helper->db->getTable('UserProfilesProfile')->findByUserIdAndTypeId($user->id, $profileTypeId);
            if(!$profile) {
                $profile = new UserProfilesProfile();
                $profile->setOwner($user);
                $profile->type_id = $profileTypeId;
                $profile->public = 0;
                $profile->setRelationData(array('subject_id'=>$user->id, 'user_id'=>$user->id));
            }
            $profile->setPostData($post);
            $this->_profile = $profile;
            if(!$profile->save(false)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Deals with files specified on the contribution form.
     *
     * @param ContributionType $contributionType Type of contribution.
     * @return array File upload array.
     */
    protected function _processFileUpload($contributionType) {
        if ($contributionType->isFileAllowed()) {
            $options = array();
            if ($contributionType->isFileRequired()) {
                $options['ignoreNoFile'] = false;
            } else {
                $options['ignoreNoFile'] = true;
            }

            $fileMetadata = array(
                'file_transfer_type' => 'Upload',
                'files' => 'contributed_file',
                'file_ingest_options' => $options
            );

            // Add the whitelists for uploaded files
            $fileValidation = new ContributionFileValidation;
            $fileValidation->enableFilter();

            return $fileMetadata;
        }
        return array();
    }

    protected function _linkItemToContributedItem($item, $contributor, $post)
    {
        $linkage = new ContributionContributedItem;
        $linkage->item_id = $item->id;
        $linkage->public = $post['contribution-public'];
        $linkage->anonymous = $post['contribution-anonymous'];
        $linkage->save();
    }

    /**
     * Adds ElementTexts to item.
     *
     * @param Item $item Item to add texts to.
     * @param array $elements Array of element inputs from form
     */
    protected function _addElementTextsToItem($item, $elements)
    {
        $db = get_db();
        $elementTable = $db->getTable('Element');
        $sql = "SELECT DISTINCT `id` from `$db->ElementSet` WHERE `record_type` = 'UserProfilesType' ";
        $userProfilesElementSets = $db->fetchCol($sql);
        foreach($elements as $elementId => $elementTexts) {
            $element = $elementTable->find($elementId);
            $elSet = $element->getElementSet();
            //need to skip over elements that are intended for a User Profile, not the item
            if (in_array($elSet->id, $userProfilesElementSets)) {
                continue;
            }
            foreach($elementTexts as $elementText) {
                if (!empty($elementText['text'])) {
                    $item->addTextForElement($element, $elementText['text']);
                }
            }
        }
    }

    /**
     * Validate the contribution form submission.
     *
     * Will flash validation errors that occur.
     *
     * Verify the validity of the following form elements:
     *      Terms agreement
     *
     * @return bool
     */
    protected function _validateContribution($post)
    {
        
        // ReCaptcha ignores the first argument.
        if ($this->_captcha and !$this->_captcha->isValid(null, $_POST)) {
            $this->_helper->flashMessenger(__('Your CAPTCHA submission was invalid, please try again.'), 'error');
            return false;
        }
                
        if ($post['terms-agree'] == 0) {
            $this->_helper->flashMessenger(__('You must agree to the Terms and Conditions.'), 'error');
            return false;
        }
        return true;
    }

    /**
     * Send an email notification to the user who contributed the Item.
     *
     * This email will appear to have been sent from the address specified via
     * the 'contribution_email_sender' option.
     *
     * @param string $email Address to send to.
     * @param Item $item Item that was contributed via the form.
     * @return void
     */
    protected function _sendEmailNotifications($recipient, $item)
    {
        $fromAddress = get_option('contribution_email_sender');
        $siteTitle = get_option('site_title');

        $this->view->item = $item;

        //If this field is empty, don't send the email
        if (!empty($fromAddress)) {
            $contributorMail = new Zend_Mail('UTF-8');
            $body .= get_option('contribution_email');
            $url = record_url($item, 'show', true);
            $link = "<a href='$url'>$url</a>";
            $body .= "<p>" . __("Contribution URL (pending review by project staff): ") . $link .  "</p>";
            $body .= "<p>" . __("Your username is %s", $recipient->username) . "</p>";
            $passwordRecoveryUrl = WEB_ROOT . "/users/forgot-password";
            $passwordRecoveryLink = "<a href='$passwordRecoveryUrl'>$passwordRecoveryUrl</a>";
            $body .= "<p>" . __("To log in and change your username, request a password here: ") . $passwordRecoveryLink . "<p>";
            $contributorMail->setBodyHtml($body);
            $contributorMail->setFrom($fromAddress, __("%s Administrator", $siteTitle ));
            $contributorMail->addTo($recipient->email);
            $contributorMail->setSubject(__("Your %s Contribution", $siteTitle));
            $contributorMail->addHeader('X-Mailer', 'PHP/' . phpversion());
            try {
                $contributorMail->send();
            } catch (Zend_Mail_Exception $e) {
                _log($e);
            }
        }

        //notify admins who want notification
        $toAddresses = explode(",", get_option('contribution_email_recipients'));
        $fromAddress = get_option('administrator_email');

        foreach ($toAddresses as $toAddress) {
            if (empty($toAddress)) {
                continue;
            }
            $adminMail = new Zend_Mail('UTF-8');
            $body = "<p>";
            $body .= __("A new contribution to %s has been made.", get_option('site_title'));
            $body .= "</p>";
            set_theme_base_url('admin');
            $url = record_url($item, 'show', true);
            $link = "<a href='$url'>$url</a>";
            $body .= "<p>" . __("Contribution URL for review: ") . $link .  "</p>";

            revert_theme_base_url();
            $adminMail->setBodyHtml($body);
            $adminMail->setFrom($fromAddress, "$siteTitle");
            $adminMail->addTo($toAddress);
            $adminMail->setSubject(__("New %s Contribution", $siteTitle ));
            $adminMail->addHeader('X-Mailer', 'PHP/' . phpversion());
            try {
                $adminMail->send();
            } catch (Zend_Mail_Exception $e) {
                _log($e);
            }
        }
    }

    protected function _createNewGuestUser($post)
    {
        $user = new User();
        $email = $post['contribution_simple_email'];
        $split = explode('@', $email);
        $name = $split[0];
        if(version_compare(OMEKA_VERSION, '2.2-dev', '<')) {
            $username = str_replace('@', 'AT', $email);
            $username = str_replace('.', 'DOT', $username);
            $user->username = $username;
        } else {
            $user->username = $email;
        }
        $user->email = $email;
        $user->name = $name;
        $user->role = 'guest';
        $user->active = 1;
        try {
            $user->save();
            //activate user so they can retrieve password without admin needing to activate first
            $activation = UsersActivations::factory($user);
            $activation->save();
        } catch(Exception $e) {
            _log($e);
        }
        return $user;
    }
}
