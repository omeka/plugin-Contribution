<?php
/**
 * @version $Id$
 * @author CHNM
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */
 
class ContributionTypeTable extends Omeka_Db_Table
{
    protected $_alias = 'ct';
    
    protected function _getColumnPairs()
    {
        return array($this->_alias . '.id', $this->_alias . '.display_name');
    }
    
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