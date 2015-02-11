<?php

class ApiImport_ResponseAdapter_OmekaNet_ContributorsAdapter extends ApiImport_ResponseAdapter_Omeka_GenericAdapter
{
    protected $recordType = 'User';
    protected $valuesAdapter;
    
    public function construct($responseData, $endpointUri, $recordType)
    {
        global $contributionImportValuesAdapter;

        parent::construct($responseData, $endpointUri, $recordType);
        if ( isset($contributionImportValuesAdapter)) {
            $this->valuesAdapter = $contributionImportValuesAdapter;
        } else {
            $contributionImportValuesAdapter = new ApiImport_ResponseAdapter_OmekaNet_ContributorValuesAdapter(null, $endpointUri, 'ElementText'); 
            $this->valuesAdapter = $contributionImportValuesAdapter;
        }

        $this->valuesAdapter->setService($this->service);
        $this->valuesAdapter->setContributorData($responseData);
    }

    public function import()
    {
        $this->record = $this->findUser();
        if (! $this->record) {
            $this->record = new User();
            $this->record->email = $this->responseData['email'];
            $this->record->name = $this->responseData['name'];
            $this->record->username = $this->responseData['email'];
            $this->record->role = 'guest';
            $this->record->save();
        }
        $this->importUserProfile();
    }
    
    protected function importUserProfile()
    {
        //from the contributor_id, go to contribution_values?contributor_id=
        //create field->element maps as needed and make 
        $this->resetService();
        $response = $this->service
                           ->contribution_contributor_values
                           ->get(array('contributor_id' => $this->responseData['id'] ));
        $valuesData = json_decode($response->getBody(), true);
        foreach ($valuesData as $data) {
            $this->valuesAdapter->resetResponseData($data);
            $this->valuesAdapter->import();
        }
    }
    
    protected function findUser()
    {
        /*
         * The earlier version of contribution made 'unique' contributors
         * by name and email. Thus name 'pat' patrick@example.com was different
         * from name 'patrick' patrick@example.com. Flattening here to unique by
         * email and not caring about data reconciliation problems
         */
        
        $email = $this->responseData['email'];
        $user = get_db()->getTable('User')->findByEmail($email);
        return $user;
    }
    
    //somewhere an id is being set on the service that I need to clobber
    protected function resetService()
    {
        $apiBaseUrl = $this->service->getApiBaseUrl();
        $key = $this->service->getKey();
        $this->service = new ApiImport_Service_Omeka($apiBaseUrl);
        $this->service->setKey($key);
    }
}