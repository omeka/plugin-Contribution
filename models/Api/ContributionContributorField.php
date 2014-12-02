<?php
class Api_ContributionContributorField extends Omeka_Record_Api_AbstractRecordAdapter
{

    public function getRepresentation(Omeka_Record_AbstractRecord $record)
    {
        $representation = array(
            'id'     => $record->id,
            'prompt' => $record->prompt,
            'type'   => $record->type,
            'order'  => $record->order
            );
        return $representation;
    }
    public function setPostData(Omeka_Record_AbstractRecord $record, $data)
    {}
    
    public function setPutData(Omeka_Record_AbstractRecord $record, $data)
    {}
}