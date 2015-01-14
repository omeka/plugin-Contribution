<?php
class ApiImport_ResponseAdapter_OmekaNet_ContributedItemsAdapter extends ApiImport_ResponseAdapter_Omeka_GenericAdapter
{
    protected $recordType = 'ContributionContributedItem';
    
    public function import()
    {
        $item = $this->localRecord('Item', $this->responseData['item']['id']);
        $user = $this->localRecord('User', $this->responseData['contributor']['id']);
        $this->record = new ContributionContributedItem();
        $this->record->item_id = $item->id;
        $this->record->public = $this->responseData['public'];
        $this->record->anonymous = 0;
        $this->record->save();
        $item->owner_id = $user->id;
        $item->save();
        $this->addOmekaApiImportRecordIdMap();
    }
}