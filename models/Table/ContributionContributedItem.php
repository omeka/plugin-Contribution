<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 * @subpackage Models
 */

/**
 * Record that keeps track of contributions; links items to contributors.
 */
class Table_ContributionContributedItem extends Omeka_Db_Table
{

    public function applySorting($select, $sortField, $sortDir)
    {
        parent::applySorting($select, $sortField, $sortDir);
        
        switch($sortField) {
            
            case 'added':
                $db = $this->getDb();
                $itemAlias = $this->getTable('Item')->getTableAlias();
                $select->join(array($itemAlias=>$db->Item), "item_id = $itemAlias.id", 'added');
                $select->order("$itemAlias.added $sortDir");                
                break;
                
                
            case 'contributor':
                $db = $this->getDb();
                $userAlias = $this->getTable('User')->getTableAlias();
                $itemAlias = $this->getTable('Item')->getTableAlias();
                if(!$select->hasJoin($itemAlias)) {
                    $select->join(array($itemAlias=>$db->Item), "item_id = $itemAlias.id", array());
                }
                
                $select->join(array($userAlias=>$db->user), "$itemAlias.owner_id = $userAlias.id", 'name');
                $select->order("$userAlias.name $sortDir");                
                break;
            
        }
        
    }
    
    public function applySearchFilters($select, $params)
    {
        
        foreach ($params as $paramName => $paramValue) {
            if ($paramValue === null || (is_string($paramValue) && trim($paramValue) == '')) {
                continue;
            }
        
            switch ($paramName) {
                case 'contributor':
                    $this->filterByContributor($select, $params['contributor']);
                    break;
                    
                case 'status':
                    $this->filterByStatus($select, $params['status']);
                    break;
            }        
        }
        
        parent::applySearchFilters($select, $params);
    }

    public function filterByStatus($select, $status)
    {
        $db = $this->getDb();
        $itemTable = $this->getTable('Item');
        $itemAlias = $itemTable->getTableAlias();
        $alias = $this->getTableAlias();
        $select->join(array($itemAlias=>$db->Item), "item_id = $itemAlias.id", array());
        switch($status) {
            case 'review':
                $select->where("$itemAlias.public = 0");
                $select->where("$alias.public = 1");
                break;
                
            case 'private':
                $select->where("$alias.public = 0");                
                break;
                
            case 'public':
                $select->where("$itemAlias.public = 1");
                $select->where("$alias.public = 1");            
                break;
        }
    }
    
    public function filterByContributor($select, $contributor)
    {
        if(is_numeric($contributor)) {
            $contributorId = $contributor;
        } else {
            $contributorId = $contributor->id;
        }
        
        $db = $this->getDb();
        $itemTable = $this->getTable('Item');
        $itemAlias = $itemTable->getTableAlias();
        if(!$select->hasJoin($itemAlias)) {
            $select->join(array($itemAlias=>$db->Item), "item_id = $itemAlias.id", array());
        }
                
        $select->where("$itemAlias.owner_id = ?", $contributorId);    
    }
    
    public function findByItem($item)
    {
        if ($item instanceof Item) {
            $itemId = $item->id;
        } else {
            $itemId = $item;
        }
        
        $select = $this->getSelect();
        $select->where('item_id = ?', $itemId, Zend_Db::PARAM_INT);
        return $this->fetchObject($select);
    }
    
    public function findAllContributors()
    {
        $db = $this->getDb();
        $userTable = $this->getTable('User');
        $select = $userTable->getSelect();
        $itemAlias = $this->getTable('Item')->getTableAlias();
        $userAlias = $this->getTable('User')->getTableAlias();
        $contribItemAlias = $this->getTableAlias();
        $select->join(array($itemAlias=>$db->Item), "$userAlias.id = $itemAlias.owner_id", array());
        $select->join(array($contribItemAlias=>$db->ContributionContributedItem), "$contribItemAlias.item_id = $itemAlias.id", array());
        $select->where("$userAlias.id = $itemAlias.owner_id");
        if(!is_allowed('Contribution_Items', 'view-anonymous')) {
            $select->where("$contribItemAlias.anonymous != 1");
        }
        
        
        $select->from(array(), "COUNT(DISTINCT('users.id'))");
        $count = $db->fetchOne($select);
        if($count > 30) {
            return $count;
        }
        
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->from(array(), $userTable->_getColumnPairs());
        
        $pairs = $this->getDb()->fetchPairs($select);
        return $pairs;           
    }
    
    public function saveContributionItemLink($itemId, $post){
        $posting = ($post['contributor_posting'] < 1)? 0 : 1;
        $db = get_db();
        $db->insert('ContributionContributedItem',
                array(
                    'item_id'=>$itemId,
                    'contributor_posting'=>$posting
                    )
                );     
    }
}
