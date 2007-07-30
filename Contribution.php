<?php 
require_once 'Contributor.php';
/**
* Contribution plugin
*/
class Contribution extends Kea_Plugin
{
	public function definition()
	{
		$this->hasMetafield("Online Submission", "Indicates whether or not this Item has been contributed from a front-end contribution form.");
		
		$this->hasMetafield("Posting Consent", "Indicates whether or not the contributor of this Item has given permission for it to be publicly available.  (Yes/No/Anonymously)");
		
		$this->hasMetafield("Submission Consent", "Indicates whether or not the contributor of this Item has given permission to submit this to the archive. (Yes/No)");
		
		$this->hasConfig("Consent Form Text", "This is the text that will appear on the consent form after a site visitor has made a contribution to the archive.");
	}
	
	public function customInstall()
	{
		$conn = $this->getDbConn();
		
		$conn->execute("CREATE TABLE `contributors` (
			`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`entity_id` BIGINT UNSIGNED NOT NULL ,
			`birth_year` YEAR NULL,
			`gender` TINYTEXT NULL,
			`race` TINYTEXT NULL,
			`occupation` TINYTEXT NULL,
			`zipcode` TINYTEXT NULL,
			`ip_address` TINYTEXT NOT NULL
			) ENGINE = MYISAM ;");
	}
	
	/**
	 * We want to delete the contributor associated with any entities that will be deleted
	 *
	 * @return void
	 **/
	public function onDeleteRecord($record)
	{
		if(get_class($record) == 'Entity')
		{
			$entity_id = $record->id;
			
			$contributor = Doctrine_Manager::getInstance()->getTable('Contributor')->findByEntity_id($entity_id);
			
			if(!$contributor) return;
			
			$contributor->delete();			
		}
	}
}

function contribution_partial()
{
	$partial = Zend::Registry( 'contribution_partial' );
	common($partial, array('data'=>$_POST), 'contribution'); 
}
 
function contribution_url($return = false)
{
	$url = generate_url(array('controller'=>'contribution','action'=>'add'), 'contribute');
	if($return) return $url;
	echo $url;
}

function link_to_contribute($text, $options = array())
{
	echo '<a href="' . contribution_url(true) . '" ' . _tag_attributes($options) . ">$text</a>";
}

function submission_consent($item)
{
	return $item->getMetatext('Submission Consent');
}

?>
