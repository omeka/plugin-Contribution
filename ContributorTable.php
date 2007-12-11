<?php 
/**
* 
*/
class ContributorTable extends Omeka_Table
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
		INNER JOIN $db->Contributor c ON c.entity_id = e.id";

		return $this->fetchObjects($sql);	
	}
}
 
?>
