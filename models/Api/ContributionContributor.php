<?php
class Api_ContributionContributor extends Omeka_Record_Api_AbstractRecordAdapter implements Zend_Acl_Resource_Interface
{
    
    public function getRepresentation(Omeka_Record_AbstractRecord $record)
    {
        $representation = array(
            'id'    => $record->id,
            'url'   => self::getResourceUrl("/contribution_contributors/{$record->id}"),
            'name'  => $record->name,
            'email' => $record->email,
            'ip_address' => $record->ip_address
            );
        return $representation;
    }
    
    public function setPostData(Omeka_Record_AbstractRecord $record, $data)
    {}
    
    public function setPutData(Omeka_Record_AbstractRecord $record, $data)
    {}
    
    public function getResourceId()
    {
        return 'Contribution_Contributors';
    }
}