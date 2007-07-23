<?php 
/**
* ContributionController
*/
class ContributionController extends Kea_Controller_Action
{		
	public function addAction()
	{
		$item = new Item;
		
		if($type = $this->_getParam('type')) {
			switch ($type) {
				case 'story':
					$partial = "_story";
					break;
				case 'image':
					$partial = "_image";
				default:
					break;
			}
		}else {
			$partial = "_story";
		}
		
		return $this->render('contribution/add.php', compact('item', 'partial'));
	}
	
	public function submitAction()
	{
		Zend::dump( $_POST );exit;
	}
	
	public function consentAction()
	{
		
	}
}
 
?>
