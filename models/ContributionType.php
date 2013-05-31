<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 * @subpackage Models
 */


require_once 'Mixin/ContributionOrder.php';

/**
 * Represents a contributable item type.
 *
 * @package Contribution
 * @subpackage Models
 */
class ContributionType extends Omeka_Record_AbstractRecord
{

    public $item_type_id;
    public $display_name;
    public $file_permissions = 'Disallowed';
    
    protected $_related = array('ContributionTypeElements' => 'getTypeElements',
                                'ItemType' => 'getItemType');

    protected function filterPostData($post)
    {
        if(empty($post['display_name'])) {
            $itemType = $this->getDb()->getTable('ItemType')->find($post['item_type_id']);
            $post['display_name'] = $itemType = $itemType->name;
        }
        return $post;
    }
    
    protected function _validate()
    {
        if(empty($this->item_type_id)) {
            $this->addError('item_type_id', 'You must select an item type.');
        }
    }

    protected function _initializeMixins()
    {
        $this->_mixins[] = new Mixin_ContributionOrder($this,
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
        return $this->file_permissions == 'Allowed'
            || $this->file_permissions == 'Required';
    }

    /**
     * Return whether file uploads are required for contributions of this type.
     *
     * @return boolean
     */
    public function isFileRequired()
    {
        return $this->file_permissions == 'Required';
    }

    /**
     * Get an array of the possible file permission levels.
     *
     * @return array
     */
    public static function getPossibleFilePermissions()
    {
        return array(
            'Disallowed' => __('Disallowed'),
            'Allowed' => __('Allowed'),
            'Required' => __('Required')
            );
    }

    /**
     * Process the type element data from the type form.
     *
     * @param ArrayObject $post
     */
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
        foreach($post['newElements'] as $index => $elementData) {
            // Skip totally empty elements
            if (!empty($elementData['prompt']) || !empty($elementData['element_set_id'])) {
                $element = new ContributionTypeElement;
                $element->type_id = $this->id;
                $element->order = count($post['Elements']) + $index;
                $element->saveForm($elementData);
            }
        }
    }

    protected function _delete()
    {
        $elements = $this->getTypeElements();

        foreach ($elements as $element) {
            $element->delete();
        }

        if (get_option('contribution_default_type') == $this->id) {
            delete_option('contribution_default_type');
        }
    }

    /**
     * Get the elements that could possibly be contributed for this type.
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
    WHERE es.name != 'Item Type Metadata'
)
UNION ALL
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
    
    public function getRecordUrl($action = 'show')
    {
        return url("contribution/types/$action/id/{$this->id}");
        return array('controller' => 'contribution/types', 'action' => $action, 'id' => $this->id);
    }
}
