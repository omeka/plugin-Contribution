<?php

// @TODO: check if this is still used?

class ApiImport_ResponseAdapter_OmekaNet_ContributorFieldsAdapter extends ApiImport_ResponseAdapter_Omeka_GenericAdapter
{
    protected $elementSet;
    protected $recordType = 'Element';

    public function import()
    {
        $elSet = $this->getElementSet();
        $this->record = new Element;
        $this->record->element_set_id = $elSet->id;
        $this->record->name = $this->responseData['prompt'];
        $this->record->description = '';
        $this->record->comment = '';
        $this->record->save();
        $this->addOmekaApiImportRecordIdMap();
    }

    protected function getElementSet()
    {
        if ($this->elementSet) {
            return $this->elementSet;
        }

        $this->elementSet = new ElementSet();
        $this->elementSet->name = "Imported Contributor Elements";
        $this->elementSet->description = "Contributor information imported from {$this->endpointUri}";
        $this->elementSet->record_type = 'UserProfilesType';
        $this->elementSet->save();

        $userProfilesType = new UserProfilesType();
        $userProfilesType->required_element_ids = serialize(array());
        $userProfilesType->required_multielement_ids = serialize(array());
        $userProfilesType->name = 'Imported Contributor Information';
        $userProfilesType->description = "Contributor information imported from {$this->endpointUri}";
        $userProfilesType->public = 0;
        $userProfilesType->required = 0;
        $userProfilesType->element_set_id = $this->elementSet->id;
        $userProfileType->save();
        return $this->elementSet;
    }
}