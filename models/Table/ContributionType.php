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
     *
     * @return array
     */
    public function getPossibleItemTypes()
    {
        return $this->getDb()->getTable('ItemType')->findPairsForSelectForm();
    }
} 
