<?php
/*
 * This cheats on the usual pattern.
 * Contributor data needs to be imported w/o reference to UserProfiles, in case
 * UP isn't active. So, this rolls through all the known contributor ids from that,
 * and looks at each /api/contribution_contributor_values?contributor_id={id}, which gives 
 * both the field and the value
 * 
 * This lets me keep a reference to the UserProfileType, ElementSet, etc
 * all in one class for the import, and look up the element, and create the ElementText 
 * all from one response of data.
 * 
 * so, don't import contributor_fields separately, do all the UserProfile, UPType, ElementSet
 * and Element creation here, invoking the other adapters as needed
 */


class ApiImport_ResponseAdapter_OmekaNet_ContributorValuesAdapter extends ApiImport_ResponseAdapter_Omeka_GenericAdapter
{
    const ELEMENT_SET_NAME = "Imported Contributor Elements";

    protected $recordType = 'ElementText'; //not really using this, because it isn't a real 1-1 onto mapping
    protected $contributorData;
    protected $userProfile;
    protected $userProfilesType;
    protected $elementSet;
    protected $element;
    protected $fieldIdElementsMap = array();
    protected $contributorIdProfileMap = array();

    public function import()
    {
        $this->getElementSet();
        $this->getUserProfilesType();
        $element = $this->getElement($this->responseData['field']['id']);
        $userProfile = $this->getUserProfile($this->responseData['contributor']);
        $this->record = new ElementText();
        $this->record->setText($this->responseData['value']);
        $this->record->html = 0;
        $this->record->element_id = $element->id;
        $this->record->record_type = 'UserProfilesProfile';
        $this->record->record_id = $userProfile->id;
        $this->record->save();
    }

    public function setContributorData($contributorData)
    {
        $this->contributorData = $contributorData;
    }

    protected function getUserProfile()
    {
        $contributorData = $this->contributorData;
        // look for the id in the cached map
        $contributorId = $contributorData['id'];
        if (array_key_exists($contributorId, $this->contributorIdProfileMap)) {
            return $this->contributorIdProfileMap[$contributorId];
        }
        $db = get_db();
        // lookup User by the email address, then get the profile
        // from user id, profile type
        $email = $contributorData['email'];
        $user = $db->getTable('User')->findByEmail($email);
        $profile = $db->getTable('UserProfilesProfile')->findByUserIdAndTypeId($user->id, $this->userProfilesType->id);
        if ($profile) {
            $this->contributorIdProfileMap[$contributorId] = $profile;
            return $profile;
        }
        //finally, create a new profile if all else failed
        $profile = new UserProfilesProfile;
        $profile->owner_id = $user->id;
        $profile->type_id = $this->userProfilesType->id;
        $profile->setRelationData(array('subject_id' => $user->id));
        $profile->public = 0;
        $profile->save(true);
        $this->contributorIdProfileMap[$contributorId] = $profile;
        return $profile;
    }

    protected function getElementSet()
    {
        if ($this->elementSet) {
            return $this->elementSet;
        }
        $elementSet = get_db()->getTable('ElementSet')->findByName(self::ELEMENT_SET_NAME);
        if ($elementSet) {
            $this->elementSet = $elementSet;
            return $elementSet;
        }
        $this->elementSet = new ElementSet();
        $this->elementSet->name = self::ELEMENT_SET_NAME;
        $this->elementSet->description = "Contributor information imported from {$this->endpointUri}";
        $this->elementSet->record_type = 'UserProfilesType';
        $this->elementSet->save();

        return $this->elementSet;
    }

    protected function getUserProfilesType()
    {
        if ($this->userProfilesType) {
            return $this->userProfilesType;
        }

        $types = get_db()->getTable('UserProfilesType')->findBy(array('label' => 'Imported Contributor Information'));

        if (count($types) != 0 ) {
            $userProfilesType = $types[0];
        } else {
            $userProfilesType = new UserProfilesType();
            $userProfilesType->required_element_ids = serialize(array());
            $userProfilesType->required_multielement_ids = serialize(array());
            $userProfilesType->label = 'Imported Contributor Information';
            $userProfilesType->description = "Contributor information imported from {$this->endpointUri}";
            $userProfilesType->public = 0;
            $userProfilesType->required = 0;
            $userProfilesType->element_set_id = $this->elementSet->id;
            $userProfilesType->save();
        }

        $this->userProfilesType = $userProfilesType;
        return $this->userProfilesType;
    }

    protected function getElement($fieldId)
    {
        if( key_exists($fieldId, $this->fieldIdElementsMap) ) {
            return $this->fieldIdElementsMap[$fieldId];
        }
        //dig up the field's prompt with (yet) another query to the source API
        $response = $this->service->contribution_contributor_fields->get($fieldId);
        // @TODO, error handling. keep it here instead of another adapter
        // to be able to cache
        $fieldData = json_decode($response->getBody(), true);

        $prompt = $fieldData['prompt'];
        $element = get_db()->getTable('Element')
                           ->findByElementSetNameAndElementName(self::ELEMENT_SET_NAME, $prompt);

        if ($element) {
            $this->fieldIdElementsMap[$fieldId] = $element;
            return $element;
        }
        //import the field as an element
        $elementSet = $this->getElementSet();
        $element = new Element;
        $element->element_set_id = $elementSet->id;
        $element->name = $prompt;
        $element->description = '';
        $element->comment = '';
        $element->save();
        $this->fieldIdElementsMap[$fieldId] = $element;
        return $element;
    }
}
