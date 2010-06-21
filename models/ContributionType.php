<?php
/**
 * @version $Id$
 * @author CHNM
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */

class ContributionType extends Omeka_Record
{
    public $item_type_id;
    public $alias;
    public $file_allowed;
    public $file_required;
    
    protected $_related = array('ContributionTypeElements' => 'getTypeElements',
                                'ItemType' => 'getItemType');
    
    protected function initializeMixins()
    {
        $this->mixins[] = new Relatable($this);
    }
    
    /**
     * Get the type elements associated with this type.
     *
     * @return array
     */
    public function getTypeElements()
    {
        return $this->_db->getTable('ContributionTypeElement')->findByType($this);
    }
    
    /**
     * Get the item type associated with this type.
     *
     * @return ItemType
     */
    public function getItemType()
    {
        return $this->_db->getTable('ItemType')->find($this->item_type_id);
    }
}
