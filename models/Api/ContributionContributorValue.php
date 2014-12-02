<?php
class Api_ContributionContributorValue extends Omeka_Record_Api_AbstractRecordAdapter
{

    public function getRepresentation(Omeka_Record_AbstractRecord $record)
    {
        $representation = array(
            'id'     => $record->id,
            'url'    => self::getResourceUrl("/contribution_contributor_values/{$record->id}"),
            'value'  => $record->value
            );

        $representation['contributor'] = array(
                'id'       => $record->contributor_id,
                'url'      => self::getResourceUrl("/contribution_contributors/{$record->contributor_id}"), 
                'resource' => 'items'
            );
        $representation['field'] = array(
            'id'       => $record->field_id,
            'url'      => self::getResourceUrl("/contribution_contributor_fields/{$record->field_id}"), 
            'resource' => 'items'
        );
        return $representation;
    }

    public function setPostData(Omeka_Record_AbstractRecord $record, $data)
    {}
    
    public function setPutData(Omeka_Record_AbstractRecord $record, $data)
    {}
}