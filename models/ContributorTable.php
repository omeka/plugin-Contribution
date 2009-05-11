<?php 
/**
 * @version $Id$
 * @copyright Center for History and New Media, 2009
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package Contribution
 **/

/**
 * Finder methods for Contributors.
 *
 * @package Contribution
 * @copyright Center for History and New Media, 2009
 **/
class ContributorTable extends Omeka_Db_Table
{	
	public function getSelect()
	{
	    $select = parent::getSelect();
	    $select->joinInner(
	        array('e'=>$this->getDb()->Entity), 
	        'e.id = c.entity_id', 
	        array("CONCAT_WS(' ', e.first_name, e.middle_name, e.last_name) as name"));
	    return $select;
	}
	
	/**
	 * Find a unique Contributor based on a hash of first/last name and email address that is provided
	 *
	 * @return Contributor|null
	 **/
	public function findByHash($firstName, $lastName, $email)
	{
	    $db = get_db();
	    
	    $sql = "SELECT c.* FROM $db->Contributor c 
	            INNER JOIN $db->Entity e ON e.id = c.entity_id
	            WHERE e.first_name = ? AND e.last_name = ? AND e.email = ? LIMIT 1";
	            
	    return $this->fetchObject($sql, array($firstName, $lastName, $email));
	}
	
	/**
	 * Retrieve all the Contributors that match a set of IDs.
	 * 
	 * @param array $ids Set of contributor IDs (integers).
	 * @return array
	 **/
	public function findByIds(array $ids)
	{
	    $select = $this->getSelect()->where('c.id IN (?)', $ids);
	    return $this->fetchObjects($select);
	}
}