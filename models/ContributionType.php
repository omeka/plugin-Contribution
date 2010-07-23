<?php
/**
 * @version $Id$
 * @author CHNM
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */

require_once 'ContributionOrderable.php';

class ContributionType extends Omeka_Record
{
    public $item_type_id;
    public $display_name;
    public $file_permissions;

    const FILE_PERMISSION_DISALLOWED = 'Disallowed';
    const FILE_PERMISSION_ALLOWED = 'Allowed';
    const FILE_PERMISSION_REQUIRED = 'Required';
    
    protected $_related = array('ContributionTypeElements' => 'getTypeElements',
                                'ItemType' => 'getItemType');
    
    protected function _initializeMixins()
    {
        $this->_mixins[] = new ContributionOrderable($this,
                'ContributionTypeElement', 'type_id', 'Elements');
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

    /**
     * Return whether file uploads are allowed for this type.
     *
     * @return boolean
     */
    public function isFileAllowed()
    {
        return $this->file_permissions == self::FILE_PERMISSION_ALLOWED
            || $this->file_permissions == self::FILE_PERMISSION_REQUIRED;
    }

    /**
     * Return whether file uploads are required for contributions of this type.
     *
     * @return boolean
     */
    public function isFileRequired()
    {
        return $this->file_permissions == self::FILE_PERMISSION_REQUIRED;
    }

    /**
     * Get an array of the possible file permission levels.
     *
     * @return array
     */
    public static function getPossibleFilePermissions()
    {
        return array(
            self::FILE_PERMISSION_DISALLOWED => self::FILE_PERMISSION_DISALLOWED,
            self::FILE_PERMISSION_ALLOWED => self::FILE_PERMISSION_ALLOWED,
            self::FILE_PERMISSION_REQUIRED => self::FILE_PERMISSION_REQUIRED
            );
    }

    public function afterSaveForm($post)
    {
        foreach($post['Elements'] as $elementId => $elementData) {
            $element = $this->getDb()->getTable('ContributionTypeElement')->find($elementId);
            if($elementData['delete']) {
                $element->delete();
            } else {
                $element->saveForm($elementData);
            }
        }
        foreach($post['newElements'] as $elementData) {
            $element = new ContributionTypeElement;
            $this->addChild($element);
            $element->saveForm($elementData);
        }
    }
}
