<?php

class ApiImport_ResponseAdapter_OmekaNet_ContributorsAdapter extends ApiImport_ResponseAdapter_Omeka_GenericAdapter
{
    protected $recordType = 'User';
    
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
        $this->addOmekaApiImportRecordIdMap();
    }
    
    protected function findUser()
    {
        $email = $this->responseData['email'];
        $user = get_db()->getTable('User')->findByEmail($email);
        return $user;
    }
}