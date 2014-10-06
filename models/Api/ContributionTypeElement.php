<?php
class Api_ContributionTypeElement extends Omeka_Record_Api_AbstractRecordAdapter implements Zend_Acl_Resource_Interface
{

    public function getRepresentation(Omeka_Record_AbstractRecord $typeElement)
    {
        $representation = array(
                'id'        => $typeElement->id,
                'url'       => self::getResourceUrl("/contribution_type_elements/{$typeElement->id}"),
                'prompt'    => $typeElement->prompt,
                'order'     => (int) $typeElement->order,
                'long_text' => (bool) $typeElement->long_text
                );
        $representation['element'] = array(
                'id'       => $typeElement->element_id,
                'url'      => self::getResourceUrl("/elements/{$typeElement->element_id}"),
                'resource' => 'elements'
                );
        $representation['type'] = array(
                'id'       => $typeElement->type_id,
                'url'      => self::getResourceUrl("/contribution_types/{$typeElement->type_id}"),
                'resource' => 'contribution_types'
                );
        return $representation;
    }
    
    public function getResourceId()
    {
        return 'Contribution_TypeElement';
    }
    
}