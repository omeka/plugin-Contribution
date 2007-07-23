<?php 
/**
* Contribution plugin
*/
class Contribution extends Kea_Plugin
{
	public function definition()
	{
		$this->hasMetafield("Online Submission", "Indicates whether or not this Item has been contributed from a front-end contribution form.");
	}
	
	
}
 
?>
