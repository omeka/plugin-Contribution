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
    const FILE_PERMISSION_DISALLOWED = 'Disallowed';
    const FILE_PERMISSION_ALLOWED = 'Allowed';
    const FILE_PERMISSION_REQUIRED = 'Required';

    public $item_type_id;
    public $display_name;
    public $file_permissions = self::FILE_PERMISSION_DISALLOWED;
    
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
            // Skip totally empty elements
            if (!empty($elementData['prompt']) || !empty($elementData['element_set_id'])) {
                $element = new ContributionTypeElement;
                $this->addChild($element);
                $element->saveForm($elementData);
            }
        }
    }

    /**
     * Gets the elements that could possibly be contributed for this type.
     * Analogous to ElementTable::getPairsForFormSelect(), except it excludes
     * the item type metadata not applicable to this specific type.
     *
     * @return array
     */
    public function getPossibleTypeElements()
    {
        $db = $this->getDb();
        $sql = <<<SQL
(SELECT e.id AS element_id, e.name AS element_name, es.name AS element_set_name
    FROM {$db->Element} AS e
        JOIN {$db->ElementSet} AS es ON e.element_set_id = es.id
        JOIN {$db->RecordType} AS rt ON es.record_type_id = rt.id
    WHERE (rt.name = 'Item' OR rt.name = 'All')
        AND es.name != 'Item Type Metadata'
)
UNION
(SELECT e.id AS element_id, e.name AS element_name, 'Item Type Metadata' AS element_set_name
    FROM {$db->Element} AS e
        JOIN {$db->ItemTypesElement} AS ite ON e.id = ite.element_id
    WHERE ite.item_type_id = ?
)
ORDER BY element_set_name ASC, element_name ASC;
SQL;
        $elements = $db->fetchAll($sql, $this->item_type_id);
        $options = array();
        foreach ($elements as $element) {
            $options[$element['element_set_name']][$element['element_id']] = $element['element_name'];
        }
        return $options;
    }
}
