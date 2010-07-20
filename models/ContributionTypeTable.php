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
        $sql = "SELECT `ct`.*, `it`.`name` AS `item_type_name`
                FROM `{$this->getTableName()}` AS `ct`
                INNER JOIN `{$db->ItemType}` AS `it`
                ON `ct`.`item_type_id` = `it`.`id`;";
        return $db->fetchAssoc($sql);
    }
} 