<?php 
/**
* 
*/
class ContributorTable extends Omeka_Db_Table
{
	public function findAll()
	{
		//Drop down to PDO for some basic processing
		$db = get_db();
			
		//Pull down a full list of all the contributors
		
		//No idea whether this will kill the app, may need to implement pagination later
		$sql = "SELECT 
			e.id,
			CONCAT_WS(' ', e.first_name, e.middle_name, e.last_name) as name, 
			e.email, c.birth_year, c.gender, c.race, c.occupation, c.zipcode, c.ip_address
		FROM $db->Entity e
		INNER JOIN $db->Contributor c ON c.entity_id = e.id ORDER BY c.id DESC";

		return $this->fetchObjects($sql);	
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