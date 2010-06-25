<?php 
/**
 * @version $Id$
 * @author CHNM
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */
 
class Contribution_IndexController extends Omeka_Controller_Action
{	
    protected $_captcha;
    
    /**
     * @var integer Represents the total # of results to display per page 
     * when browsing the list of contributors.
     * 
     * This also takes advantage of the built-in 'browse' action of the base
     * class, which displays a list of Contributors.  Access to that action is
     * controlled via the ACL.
     * @see Omeka_Controller_Action::browseAction()
     */
    protected $_browseRecordsPerPage = CONTRIBUTORS_PER_PAGE;
    
    /**
     * Set up the Contribution controller.
     * 
     * Instantiates a session namespace for passing data between the 3 pages of
     * the form on the Contribution plugin.
     * 
     * TODO: Should not strip tags (all data entered will not be displayed as HTML,
     * so it shouldn't be stripped).
     * @return void
     **/
	public function init()
	{
		require_once 'Zend/Session.php';
		$this->session = new Zend_Session_Namespace('Contribution');		
	}
	
	/**
	 * Action for main contribution form.
	 */
	public function contributeAction()
	{
	    if (!isset($_POST['form-submit'])) {
	        $this->_captcha = $this->_setupCaptcha();
            if ($this->_captcha) {
                $this->view->captchaScript = $this->_captcha->render(new Zend_View);
            }
	    } else {
	        //$item = new Item;
	        //$item->saveForm($_POST);
	        $this->_processForm();
	        echo var_dump($_POST);
	        die();
	    }
	    if (isset($_POST['submit-type'])) {
	        $this->_setupContributeSubmit();
	        $this->view->typeForm = $this->view->render('index/type-form.php');
	    }
	}
	
	/**
	 * Action for AJAX request from contribute form.
	 */
	public function typeFormAction()
	{
	    $this->_setupContributeSubmit();
	}
	
	/**
	 * Common tasks whenever displaying submit form for contribution.
	 */
	public function _setupContributeSubmit()
	{
	    // Override default element form display	    
		$this->view->addHelperPath(CONTRIBUTION_HELPERS_DIR, 'Contribution_View_Helper');
	    $item = new Item;
		$this->view->item = $item;
        
        $typeId = $_POST['contribution_type'];
	    $type = get_db()->getTable('ContributionType')->find($typeId);
	    $this->view->type = $type;
	}
	
	/**
	 * Creates the reCAPTCHA object and returns it.
	 * 
	 * @return Zend_Captcha_Recaptcha|null
	 */
	protected function _setupCaptcha()
	{
	    $publicKey = get_option('contribution_recaptcha_public_key');
	    $privateKey = get_option('contribution_recaptcha_private_key');

	    if (empty($publicKey) or empty($privateKey)) {
	       return;
	    }
	    
        // Originating request:
        $captcha = new Zend_Captcha_ReCaptcha(array(
            'pubKey' => $publicKey, 
            'privKey' => $privateKey));

        return $captcha;
	}
	
	/**
	 * Handle the POST for adding an item via the public form.
	 * 
	 * Validate and save the contribution to the database.  Save the ID of the
	 * new item to the session.  Redirect to the consent form. 
	 * 
	 * If validation fails, render the Contribution form again with errors.
	 * @return void
	 */
	protected function _processForm()
	{		
	    /**
	     * Internal testing ONLY! Form submissions are not currently validated
	     */
		if (!empty($_POST)) {
			try {
			    $item = new Item;
			    
			    $contributionTypeId = $_POST['contribution_type'];
			    $contributionType = get_db()->getTable('ContributionType')->find($contributionTypeId);
			    $itemTypeId = $contributionType->getItemType()->id;				
			    $item->public = false;
                $item->featured = false;
                $item->item_type_id = $itemTypeId;
				$collectionId = get_option('contribution_collection_id');
				if (!empty($collectionId) && is_numeric($collectionId)) {
				    $item->collection_id = (int) $collectionId;
				}
				/*
				if (!$this->_validateContribution($creatorName)) {
                    return false;
                }*/
                
				$elementTable = get_db()->getTable('Element');
				$elements = $_POST['Elements'];
				foreach($elements as $elementId => $elementTexts) {
				    $element = $elementTable->find($elementId);
				    foreach($elementTexts as $elementText) {
				        $item->addTextForElement($element, $elementText['text']);
				    }
				}
				$item->save();
				/*
				// Add the text for Document item types, if necessary.    
												
				$contributor = $this->_createOrFindContributor();
				Zend_Registry::set('contributor', $contributor);
				
				// If a particular contribution type implies (requires) a file
				// to be uploaded, add necessary options for insert_item().
				// FIXME: This wouldn't account for situations where uploads are
				// optional, such as documents.
				if ($this->_uploadedFileIsRequired($_POST['type'])) {
				    $fileUploadOptions = array(
                        'files' => 'contributed_file', // Form input name
                        'file_transfer_type' => 'Upload',
                        'file_ingest_options' => array('ignoreNoFile'=> false));
				} else {
				    $fileUploadOptions = array();
				}
				
				// Add the whitelists for uploaded files.
				$fileValidation = new Contribution_FileValidation($itemMetadata['item_type_name']);
				$fileValidation->enableFilter();
                                				
				// Needed to tag the items properly.
				$itemMetadata['tag_entity'] = $contributor->Entity;					
				try {
				    $item = insert_item($itemMetadata, 
					                    $elementTexts, 
					                    $fileUploadOptions);
				} catch (Omeka_File_Ingest_InvalidException $e) {
				    // HACK: grep the exception to determine whether this is
				    // related to file uploads.
				    if (strstr($e->getMessage(), 
				               "The file 'contributed_file' was not uploaded")) {
				       $this->flashError("File: A file must be uploaded.");
				    } else {
				        $this->flashError($e->getMessage());
				    }
				} catch (Exception $e) {
				    $this->flashError($e->getMessage());
				} */
				return true;
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
	 * Display a "Thank You" message to users who have contributed an item 
	 * through the public form.
	 * 
	 * Redirects here from the 'submit' action.
	 * 
	 * @see Contribution_IndexController::submitAction()
	 * @return void
	 **/
	public function thankyouAction()
	{

	}
	
	/**
	 * Retrieve or create a new Contributor record based on parameters passed 
	 * through the POST. 
	 * 
	 * This takes first name, last name and email address from the POST.  With 
	 * that, it searches the database for an existing contributor with all those
	 * properties.  Returns that, otherwise creates a new Contributor with all 
	 * of those properties.
	 * 
	 * @return Contributor
	 **/	
	protected function _createOrFindContributor()
	{
		//Verify that form submissions involve nothing sneaky by grabbing specific parts of the input
		$contrib = $_POST['contributor'];
        
        $firstName = $contrib['first_name'];
        $lastName = $contrib['last_name'];
        $email = $contrib['email'];
                
        //Try to locate an existing contributor entry based on a hash of first / last / email address
        $contributor = get_db()->getTable('Contributor')->findByHash($firstName, $lastName, $email);

        if (!$contributor) { 
    		$contributor = new Contributor;
    		$contributor->createEntity($contrib);
    		$contributor->setArray($contrib);
        }

		return $contributor;
	}
	
	/**
	 * Validate the contribution form submission.
	 * 
	 * Will flash validation errors that occur.
	 * 
	 * Verify the validity of the following form elements:
	 *      Captcha (if exists)
	 *      Posting Consent
	 *      Story text (if exists)
	 *      Creator Name
	 * 
	 * FIXME: Calling flashError() multiple times needs to display all of the 
	 * flashed errors.
	 * @return boolean
	 **/
	protected function _validateContribution($creatorName)
	{
	    $isValid = true;
	    
	    $errors = array();
	    
	    // ReCaptcha ignores the first argument.
	    if ($this->_captcha and !$this->_captcha->isValid(null, $_POST)) {
            $errors[] = 'Your CAPTCHA submission was invalid, please try again.';
            $isValid = false;
	    }	    

		//Don't trust the post content!
		if(!in_array($_POST['posting_consent'], array('Yes', 'No', 'Anonymously'))) {
			$errors[] = 'Invalid posting consent given!';
			$isValid = false;
		}
        
        $storyText = trim($_POST['text']);
        if (array_key_exists('text', $_POST) and empty($storyText)) {
            $errors[] = 'Story: Please provide the text of the story.';
            $isValid = false;
        }
        
        $creatorName = trim($creatorName);
        if (empty($creatorName)) {
		    $errors[] = 'Creator: Please provide a valid name for the creator.';
		    $isValid = false;
		}
		
        if ($errors) {
            $this->flashError(join("\n", $errors));
        }
		
		return $isValid;
	}
	
	/**
	 * Submit the consent form that accompanies every new contribution.
	 * 
	 * This determines whether or not consent has been given, retrieves the 
	 * Item based on the ID passed to the session, updates the Dublin Core 
	 * 'Rights' and Contribution Form 'Submission Consent' with the appropriate
	 * data, sends an email notification, and redirects to the 'thankyou' page.
	 * 
	 * The consent form itself is part of the 'consent' action.
	 * 
	 * NOTE: The 'Rights' field does not currently store that text as HTML.
	 * 
	 * FIXME: Does this spit errors if someone tries to access it without having
	 * contributed a specific item?
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
        
        // Session needs to save either the item ID # (to retrieve it later) or the
        // Item record itself (to manipulate later). Using the item ID causes
        // problems b/c the system can't access a non-public item through the DB
        // when no user is logged in. The following is a hack that involves
        // granting temporary ACL privileges just to the consent form so the script
        // has enough permissions to tweak the metadata for a private item.
        // 
        // IMPORTANT TO REMEMBER: saving the item record in the session bonks the
        // mysqli database object so that it's unusable for further data
        // manipulation (seems like a PHP bug). This might be fixable in 1.0 (by
        // disconnecting the database object from the record), need more info.
        get_acl()->allow(null, 'Items', 'showNotPublic');
        
        $item = update_item($itemId, array('overwriteElementTexts'=>true), array(
            'Dublin Core'=>array(
                'Rights'=>array(array('text'=>(string)get_option('contribution_consent_text'), 'html'=>true))),
            'Contribution Form'=>array(
                'Submission Consent'=>array(array('text'=>$submission_consent, 'html'=>false)))
            ));
		
		$this->_sendEmailNotification($session->email, $item);
		
		unset($session->itemId);
		unset($session->email);
				
		$this->redirect->gotoRoute(array('action'=>'thankyou'), 'contributionLinks');
	}
	
	/**
	 * Send an email notification to the user who contributed the Item.
	 * 
	 * This email will appear to have been sent from the address specified via
	 * the 'contribution_notification_email' option.
	 * 
	 * FIXME: Coding standards.
	 * @param string $email Address to send to.
	 * @param Item $item Item that was contributed via the form.
	 * @return void
	 **/
	protected function _sendEmailNotification($toEmail, $item)
	{
		$fromEmail = get_option('contribution_notification_email');
		
		//If this field is empty, don't send the email
		if(empty($fromEmail)) {
			return;
		}
		
		$this->view->item = $item;
		$item->view->email = $toEmail;
				
		$mail = new Zend_Mail();
        $mail->setBodyText($this->view->render('index/email.php'));
        $mail->setFrom($fromEmail, get_option('site_title') . ' Administrator');
        $mail->addTo($toEmail);
        $mail->setSubject("Your " . get_option('site_title') . " Contribution");
        $mail->send();
	}
	
	/**
	 * Display the consent form for any items contributed through the public 
	 * form.
	 * 
	 * All successful form submissions will redirect to this action.  This 
	 * action contains a form that POSTs to the 'submit' action.
	 *
	 * @return void
	 **/
	public function consentAction()
	{		
		
	}
	
	/**
	 * Batch processing for contributors.
	 * 
	 * Currently available actions include 'delete'.
	 **/
	public function batchAction()
	{
	    $req = $this->getRequest();
	    $errorMsg = null;
	    
	    if ($req->getMethod() != 'POST') {
	        $errorMsg = "Batch processing requires POST!";
	    }
	    
	    // Update this list if more are added.
	    $validActions = array('delete');
	    
	    if (!($action = $req->get('batch_action')) ||
	        !in_array($action, $validActions)) {
	        $errorMsg = "Batch processing must have a valid action!";
	    }
	    
	    if (!($contributorIds = $req->get('contributor_id')) ||
	        !is_array($contributorIds)) {
	        $errorMsg = "Batch processing must be given a list of contributor IDs!";    
	    }
	    
	    // Check permissions for each batch action.
	    if (!$this->isAllowed($action)) {
	       $errorMsg = "Insufficient permissions for action given!";
	    }
	    
	    // Put out the fire.
	    if ($errorMsg) {
	       $this->flashError($errorMsg);
	       $this->_helper->redirector->goto('browse');
	    }
	    
	    $this->_forward($action);
	}	
}