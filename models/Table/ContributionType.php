<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 * @subpackage Models
 */

/**
 * Table for ContributionType objects.
 *
 * @package Contribution
 * @subpackage Models
 */
class Table_ContributionType extends Omeka_Db_Table
{

    /**
     * Used to create options for HTML select form elements.
     *
     * @return array
     */
    protected function _getColumnPairs()
    {
        $alias = $this->getTableAlias();
        return array($alias . '.id', $alias . '.display_name');
    }    
    
    /**
     * Get an array of type data, along with pertinent item type data.
     *
     * @return array
     */
    public function getBrowseData()
    {
        $db = $this->getDb();
        $sql = <<<SQL
SELECT `ct`.*, `it`.`name` AS `item_type_name`
    FROM `{$this->getTableName()}` AS `ct`
    INNER JOIN `{$db->ItemType}` AS `it`
    ON `ct`.`item_type_id` = `it`.`id`;
SQL;
        return $db->fetchAssoc($sql);
    }

    /**
     * Get an array of possible item types for a new contribution type.
     * "Possible types" here means any item type not currently used as the
     * basis for a contribution type.
     *
     * @return array
     */
    public function getPossibleItemTypes()
    {
        $db = $this->getDb();
        $sql = <<<SQL
SELECT `it`.`id` AS `item_type_id`, `it`.`name` AS `item_type_name`
    FROM `{$db->ItemType}` AS `it`
    WHERE NOT `it`.`id` IN (SELECT `item_type_id` FROM `{$this->getTableName()}`)
    ORDER BY `item_type_name` ASC;
SQL;
        $itemTypes = $db->fetchAll($sql);
        $options = array();
        foreach ($itemTypes as $itemType) {
            $options[$itemType['item_type_id']] = $itemType['item_type_name'];
        }
        return $options;
    }
} 
