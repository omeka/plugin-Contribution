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
	
	public function applySearchFilters($select, $params)
	{
	    if (array_key_exists('per_page', $params)) {
	       $page = (int)$params['page'] or $page = 1;
	       $perPage = (int)$params['per_page'];
	       $select->limitPage($page, $perPage);
	    }
	}

	/**
	 * Bug fix that may need to be incorporated into the main code base when
	 * pagination is built into the table classes by default.
	 * 
	 * This will unset the 'per_page' parameter for counts so that OFFSET is not
	 * applied to the SELECT COUNT() query, which would cause the query to always
	 * return false.
	 * 
	 * @param array
	 * @return int
	 **/
	public function count($params)
	{
	    unset($params['per_page']);
	    unset($params['page']);
	    return parent::count($params);
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
}