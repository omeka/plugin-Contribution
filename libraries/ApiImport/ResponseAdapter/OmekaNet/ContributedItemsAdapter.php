<?php
class ApiImport_ResponseAdapter_OmekaNet_ContributedItemsAdapter extends ApiImport_ResponseAdapter_Omeka_GenericAdapter
{
    protected $recordType = 'ContributionContributedItem';
    protected $contributorData;
    public function import()
    {
        $item = $this->localRecord('Item', $this->responseData['item']['id']);
        
        if ($item) {
            //can't use localRecord, because of the map from contributor to user. ids won't match
            $user = $this->findUser();
    
            $this->record = new ContributionContributedItem();
            $this->record->item_id = $item->id;
            $this->record->public = $this->responseData['public'];
            $this->record->anonymous = 0;
            $this->record->save();
            $item->owner_id = $user->id;
            $item->save();
            $this->addOmekaApiImportRecordIdMap();            
        } else {
            _log('Skipped contribution with no public item');
        }

    }
    
    protected function getContributor()
    {
        $contributorId = $this->responseData['contributor']['id'];
        $response = $this->service->contribution_contributors->get($contributorId);
        if ($response->getStatus() == 200) {
            $this->contributorData = json_decode($response->getBody(), true);
        } else {
            _log($response->getMessage());
        }
    }
    
    protected function findUser()
    {
        $this->getContributor();
        if ($this->contributorData) {
            $email = $this->contributorData['email'];
            $user = get_db()->getTable('User')->findByEmail($email);
            return $user;            
        }
    }
}