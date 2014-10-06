<?php
class Api_ContributionContributedItem extends Omeka_Record_Api_AbstractRecordAdapter implements Zend_Acl_Resource_Interface
{

    public function getRepresentation(Omeka_Record_AbstractRecord $contributedItem)
    {
        $representation = array(
                'id'         => $contributedItem->id,
                'url'        => self::getResourceUrl("/contributions/{$contributedItem->id}"),
                'public'     => (bool) $contributedItem->public,
                'anonymous'  => (bool) $contributedItem->anonymous
                );
        $representation['item'] = array(
                'id'       => $contributedItem->item_id, 
                'url'      => self::getResourceUrl("/items/{$contributedItem->item_id}"),
                'resource' => 'items'
               );
        return $representation;
    }

    public function getResourceId()
    {
        return 'Contribution_ContributedItem';
    }
}