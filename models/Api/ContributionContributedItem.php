<?php
class Api_ContributionContributedItem extends Omeka_Record_Api_AbstractRecordAdapter implements Zend_Acl_Resource_Interface
{
    public function getRepresentation(Omeka_Record_AbstractRecord $record)
    {
        $representation = array(
            'id'             => $record->id,
            'url'            => self::getResourceUrl("/contribution_contributed_items/{$record->id}"),
            'public'         => $record->public
            );
        
        $representation['item'] = array(
                'id'       => $record->item_id,
                'url'      => self::getResourceUrl("/items/{$record->item_id}"), 
                'resource' => 'items'
            );
        
        $representation['contributor'] = array(
                'id'       => $record->contributor_id,
                'url'      => self::getResourceUrl("/contribution_contributors/{$record->contributor_id}"), 
                'resource' => 'contribution_contributors'
            );
        return $representation;
    }
    
    public function setPostData(Omeka_Record_AbstractRecord $record, $data)
    {}
    
    public function setPutData(Omeka_Record_AbstractRecord $record, $data)
    {}
    
    public function getResourceId()
    {
        return 'Contribution_ContributedItem';
    }
}