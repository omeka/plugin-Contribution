<?php 
/**
* ContributionController
*/
class ContributionController extends Omeka_Controller_Action
{	
	public function init()
	{
		$this->session = new Zend_Session_Namespace('Contribution');
		
		//The admin interface allows inserting HTML tags into the text of the items, but the Contribution plugin shouldn't allow that.
		$_POST = strip_tags_recursive($_POST);
		
		$this->_modelClass = 'Contributor';		
	}
		
	public function addAction()
	{
		$item = new Item;
		
		if($this->processForm($item))
		{
			$this->_redirect('contribution/consent');
		}else {
			return $this->renderContributeForm($item);
		}		
	}
	
	//Can't delete Contributors
	public function deleteAction()
	{
		return $this->_forward('add');
	}
	
	public function thankyouAction()
	{
		$this->render('contribution/thankyou.php');
	}
	
	/**
	 * Browse a list of contributors
	 *
	 * @return void
	 **/
	public function contributorsAction()
	{
		//Put a quick permissions check in here		
		if(!$this->isAllowed('add','Entities')) {
			return $this->forbiddenAction();
		}

		$contributors = $this->_table->findAll();
		
		$this->render('contribution/contributors.php', compact('contributors'));
	}
	
	protected function renderContributeForm($item)
	{
		if($type = $this->_getParam('type')) {
			switch ($type) {
				case 'Document':
					$partial = "_document";
					break;
				case 'Still Image':
				case 'Moving Image':
				case 'Sound':
					$partial = "_file";
					break;
				default:
					$partial = "_document";
					break;
			}

		}else {
			$partial = "_document";
		}
		
		Zend_Registry::set('contribution_partial', $partial);
		
		return $this->render('contribution/add.php', compact('item'));		
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
				//Manipulate the array that will be processed by commitForm()
				$clean = array();
				
				
				$clean['title'] = $_POST['title'];
				$clean['description'] = $_POST['description'];				
				
				//@todo Change how the creator/contributor info is set if we ever implement it as entity relationships
				//Right now it is either the contributor who posted the item or it is whatever is in the text field
				$clean['contributor'] = $_POST['contributor']['first_name'] . ' ' . $_POST['contributor']['last_name'];
				$clean['creator'] = $_POST['contributor_is_creator'] ? $clean['contributor'] : $_POST['creator'];
				
		/*
					Zend::dump( $clean );
				Zend::dump( $_POST );exit;
		*/	
				//Create an entity using the data provided on the form and pass it as an option to the commitForm() call
				
				$contributor = $this->createOrFindContributor();
				
				//Give the item the correct Type (find it by name, then assign)
				$type = $this->getTable('Type')->findBySql('name = ?', array($_POST['type']), true);
			
				if(!$type) {
					throw new Omeka_Validator_Exception( "Invalid type named {$_POST['type']} provided!");
				}

				$item->type_id = $type->id;
				
				//Handle the metatext
					//Document text (if applicable)
					//Posting Consent
					//Submission Consent
				if(!empty($_POST['text'])) {
					$item->setMetatext('Text', $_POST['text']);
				}
				
				//At this point we should set the submission consent to No (just in case it doesn't make it to the page)
				$item->setMetatext('Submission Consent', 'No');
				
				//Don't trust the post content!
				if(!in_array($_POST['posting_consent'], array('Yes', 'No', 'Anonymously'))) {
					throw new Omeka_Validator_Exception( 'Invalid posting consent given!' );
				}
				
				$item->setMetatext('Posting Consent', $_POST['posting_consent']);
				$item->setMetatext('Online Submission', 'Yes');
												
				if($item->saveForm($clean)) {
					
					$item->addTags($_POST['tags'], $contributor->Entity);
					$item->setAddedBy($contributor->Entity);

					//Put item in the session for the consent form to use
					$this->session->item = $item;
					$this->session->email = $_POST['contributor']['email'];
					
					return true;
				}else {
					return false;
				}	
				
				
			} catch (Omeka_Validator_Exception $e) {
				$this->flashValidationErrors($e);
				return false;
			}
		}
		return false;
	}
	
	/**
	 * Final submission, add the consent info and redirect to a thank-you page
	 *
	 * @return void
	 **/
	public function submitAction()
	{		
		$session = $this->session;

		$item = $session->item;
		
		$item->rights = $_POST['rights'];
		
		$submission_consent = $_POST['submission_consent'];
		
		if(!in_array($submission_consent, array('Yes','No'))) {
			$submission_consent = 'No';
		}
		
		$item->setMetatext('Submission Consent', $submission_consent);
		
		$item->save();
		
		$this->sendEmailNotification($session->email, $item);
		
		unset($session->item_id);
		unset($session->email);
		
		//If they did not give their consent, it makes no sense to send them to the 'thankyou' page
		if($submission_consent == 'No') {
			$this->_redirect('');
		}
		
		$this->_redirect('contribution/thankyou');
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
		$this->render('contribution/consent.php');
	}
}
 
?>
