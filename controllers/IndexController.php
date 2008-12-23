<?php 
require_once 'Contributor.php';

class Contribution_IndexController extends Omeka_Controller_Action
{	
	public function init()
	{
		$this->_modelClass = 'Contributor';		

		require_once 'Zend/Session.php';
		$this->session = new Zend_Session_Namespace('Contribution');
		
		//The admin interface allows inserting HTML tags into the text of the items, but the Contribution plugin shouldn't allow that.
		//todo: replace this with the HTMLPurifier plugin
		$_POST = $this->strip_tags_recursive($_POST);
	}
	
	private function strip_tags_recursive($input)
	{
		return is_array($input) ?  array_map(array($this, __FUNCTION__), $input) : strip_tags($input);
	}
		
	public function addAction()
	{
		$item = new Item;
		
		if($this->processForm($item))
		{
			$this->redirect->gotoRoute(array('action'=>'consent'), 'contributionLinks');
		}else {
            $this->view->item = $item;
		}		
	}
	
	//Can't delete Contributors
	public function deleteAction()
	{
		return $this->_forward('add');
	}
	
	public function thankyouAction()
	{

	}
		
	protected function createOrFindContributor()
	{
		//Verify that form submissions involve nothing sneaky by grabbing specific parts of the input
		$contrib = $_POST['contributor'];
        
        $firstName = $contrib['first_name'];
        $lastName = $contrib['last_name'];
        $email = $contrib['email'];
                
        //Try to locate an existing contributor entry based on a hash of first / last / email address
        $contributor = get_db()->getTable('Contributor')->findByHash($firstName, $lastName, $email);

        if(!$contributor) {
            
    		$contributor = new Contributor;
		
    		$contributor->createEntity($contrib);
		
    		$contributor->setArray($contrib);
		
    		$contributor->forceSave();            
        }

		return $contributor;
	}

	/**
	 * Validate and save the contribution to the DB, save the new item in the session
	 * then redirect to the consent form, 
	 * otherwise render the contribution form again
	 *
	 * @return void
	 **/
	protected function processForm($item)
	{		
		if(!empty($_POST)) {
			if(array_key_exists('pick_type', $_POST)) return false;
			
			try {				
				//Don't trust the post content!
				if(!in_array($_POST['posting_consent'], array('Yes', 'No', 'Anonymously'))) {
					throw new Omeka_Validator_Exception( 'Invalid posting consent given!' );
				}
				
				$itemMetadata = array(
				    'public'=>false,
				    'featured'=>false,
				    // Do not set the collection_id.
				    'item_type_name'=>$_POST['type'],
				    'tags'=>$_POST['tags']);
				
				$contributorName = $_POST['contributor']['first_name'] . ' ' . $_POST['contributor']['last_name'];
				
				$elementTexts = array(
				    'Dublin Core'=>array(
				        'Title'=>array(array(
				            'text'=>(string)$_POST['title'], 
				            'html'=>false)),
				        'Description'=>array(array(
				            'text'=>(string)$_POST['description'], 
				            'html'=>false)),
				        'Contributor'=> array(array(
				            'text'=>$contributorName,
				            'html'=>false)),
				        'Creator'=>array(array(
				            'text'=>$_POST['contributor_is_creator'] ? $contributorName : (string)$_POST['creator'],
				            'html'=>false))),
				    'Contribution Form'=>array(
				        'Online Submission'=>array(array(
				            'text'=>'Yes', // We're submitting through the contribution form.
				            'html'=>false)),
				        'Submission Consent'=>array(array( 
				            'text'=>'No', // This will be overridden by the consent form.
				            'html'=>false)),
				        'Posting Consent'=>array(array(
				            'text'=>$_POST['posting_consent'],
				            'html'=>false)))
				    );
				
				// Add the text for Document item types, if necessary.    
				if (array_key_exists('text', $_POST)) {
				    $elementTexts['Item Type Metadata']['Text'][] = array('text'=>$_POST['text'], 'html'=>false);
				}	
												
				$contributor = $this->createOrFindContributor();
				
				// Needed to tag the items properly.
				$itemMetadata['tag_entity'] = $contributor->Entity;					
				$item = contribution_insert_item($itemMetadata, $elementTexts);
																				
				if($item->exists()) {
				    // Also this is needed, apparently.
					$item->setAddedBy($contributor->Entity);

					//Put item in the session for the consent form to use
					$this->session->itemId = $item->id;
					$this->session->email = $_POST['contributor']['email'];
					
					// Success.
					return true;
				}else {
				    // Failure?  Should this even get here?  It should probably throw if the item doesn't save.
					return false;
				}	
				
				
			} catch (Omeka_Validator_Exception $e) {
				$this->flashValidationErrors($e);
				// Validation errors.
				return false;
			}
		}
		// No POST.
		return false;
	}
	
	/**
	 * Final submission, add the consent info and redirect to a thank-you page
	 *
	 * @return void
	 **/
	public function submitAction()
	{		
	
		$submission_consent = $_POST['contribution_submission_consent'];
		
		if(!in_array($submission_consent, array('Yes','No'))) {
			$submission_consent = 'No';
		}
		
		//If they did not give their consent, redirect them to a new contribution page.
		if($submission_consent == 'No') {
			$this->redirect->gotoRoute(array(), 'contributionAdd');
		}
		
		$session = $this->session;
		$itemId = $session->itemId;
        
        if (!is_int($itemId)) {
            throw new Exception('Cannot provide consent without first contributing an item!');
        }
        
        $item = contribution_update_item($itemId, array('overwriteElementTexts'=>true), array(
            'Dublin Core'=>array(
                'Rights'=>array(array('text'=>$_POST['contribution_consent_text'], 'html'=>false))),
            'Contribution Form'=>array(
                'Submission Consent'=>array(array('text'=>$submission_consent, 'html'=>false)))
            ));
		
		$this->sendEmailNotification($session->email, $item);
		
		unset($session->itemId);
		unset($session->email);
				
		$this->redirect->gotoRoute(array('action'=>'thankyou'), 'contributionLinks');
	}
	
	public function partialAction() 
	{
		$contributionType = $this->_getParam('contributiontype');
		switch ($contributionType) {
			case 'Document':
				$partial = "-document";
				break;
			case 'Still Image':
			case 'Moving Image':
			case 'Sound':
				$partial = "-file";
				break;
			default:
				$partial = "-document";
				break;
		}
		$this->render($partial);
	}
	
	protected function sendEmailNotification($email, $item)
	{
		$from_email = get_option('contribution_notification_email');
		
		//If this field is empty, don't send the email
		if(empty($from_email)) {
			return;
		}
		
		$item_url = WEB_ROOT . DIRECTORY_SEPARATOR . 'items/show/' . $item->id;
		
		$body = "Thank you for your contribution to " . get_option('site_title') . ".  Your contribution has been accepted and will be preserved in the digital archive. For your records, the permanent URL for your contribution is noted at the end of this email. Please note that contributions may not appear immediately on the website while they await processing by project staff.
			
Contribution URL (pending review by project staff):\n\n\t$item_url";
		
		$title = "Your " . get_option('site_title') . " Contribution";
  		
		$header = "From: " . $from_email . "\r\n" . 'X-Mailer: PHP/' . phpversion();
//var_dump( array($email, $title, $body, $header) );exit;		
		$res = mail( $email, $title, $body, $header);		
	}
	
	/**
	 * Add the body of the consent form to the rights field for the item, 
	 * if applicable.  Set Submission Consent metatext to the form value
	 *
	 * @return void
	 **/
	public function consentAction()
	{		
		$this->render('consent');
	}
}