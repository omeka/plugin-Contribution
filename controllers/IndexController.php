<?php 
/**
 * @version $Id$
 * @copyright Center for History and New Media, 2009
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package Contribution
 **/

/**
 * 
 *
 * @package Contribution
 * @copyright Center for History and New Media, 2009
 **/
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
		$this->_modelClass = 'Contributor';		

		require_once 'Zend/Session.php';
		$this->session = new Zend_Session_Namespace('Contribution');		
	}
	
	/**
	 * Add (contribute) a new item through a public-facing form.
	 * 
	 * Accessible only through the public interface.
	 * 
	 * @return void
	 **/	
	public function addAction()
	{
		$item = new Item;
		
		$this->_captcha = $this->_setupCaptcha();
		
		if($this->_processForm($item))
		{
			$this->redirect->gotoRoute(array('action'=>'consent'), 'contributionLinks');
		}else {
            $this->view->item = $item;
            if ($this->_captcha) {
                // Requires a blank Zend_View instance b/c ZF forces it to.
                $this->view->captchaScript = $this->_captcha->render(new Zend_View);
            }
		}		
	}
	
	/**
	 * Delete an existing Contributor (and all the items, if specified).
	 **/
	public function deleteAction()
	{
	    $req = $this->getRequest();
	    $contributorIds = $req->get('contributor_id');
	    
	    // Check for a flag submitted from the confirmation form to see if we need
	    // to actually delete this stuff.
	    if ($req->has('do_delete')) {	       
	       $deleteItems = (boolean)$req->get('delete_items');
	       // Compressed into a string on the confirm form.
	       $contributorIds = explode(',', $contributorIds);
	       
	       if (empty($contributorIds)) {
	           $this->flashError("No contributors were scheduled for deletion.");
	           $this->_helper->redirector->goto('browse');
	       }
	       
	       $contributors = $this->getTable()->findByIds($contributorIds);
	       foreach ($contributors as $contributor) {
	           $contributor->delete($deleteItems);
	       }
	       
	       // Give us a nice message to let us know it worked.
	       $successMsg = count($contributors) . " contributor(s) " 
	                   . ($deleteItems ? " and their associated items " : "") 
	                   . "were successfully deleted.";
	       $this->flashSuccess($successMsg);
	       
	       // Go back to the browse page.
	       $this->_helper->redirector->goto('browse');
	    }
	    
	    $this->view->contributors = $this->getTable()->findByIds($contributorIds);
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

        if(!$contributor) {
            
    		$contributor = new Contributor;
		
    		$contributor->createEntity($contrib);
		
    		$contributor->setArray($contrib);
        }

		return $contributor;
	}
            
	/**
	 * Handle the POST for adding an item via the public form.
	 * 
	 * Validate and save the contribution to the database.  Save the ID of the
	 * new item to the session.  Redirect to the consent form. 
	 * 
	 * If validation fails, render the Contribution form again with errors.
	 *
	 * FIXME: Split this into smaller methods.
	 * TODO: Make sure this still works without Javascript.
	 * @return void
	 **/
	protected function _processForm($item)
	{		
		if(!empty($_POST)) {
		    		    
			if(array_key_exists('pick_type', $_POST)) return false;
					
			try {				
				
				$itemMetadata = array(
				    'public'=>false,
				    'featured'=>false,
				    // Do not set the collection_id.
				    'item_type_name'=>$_POST['type'],
				    'tags'=>$_POST['tags']);
				
				$collectionId = get_option('contribution_collection_id');
				if (!empty($collectionId) && is_numeric($collectionId)) {
				    $itemMetadata['collection_id'] = (int) $collectionId;
				}
				
				$contributorName = $_POST['contributor']['first_name'] . ' ' . $_POST['contributor']['last_name'];
				
				$creatorName = $_POST['contributor_is_creator'] ? $contributorName : (string)$_POST['creator'];
				
				if (!$this->_validateContribution($creatorName)) {
                    return false;
                }
								
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
				            'text'=>$creatorName,
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
				            'html'=>false)),
				        'Contributor is Creator'=>array(array(
				            'text'=>$_POST['contributor_is_creator'] ? 'Yes' : 'No')))
				    );
				
				// Add the text for Document item types, if necessary.    
				if (array_key_exists('text', $_POST)) {
				    $elementTexts['Item Type Metadata']['Text'][] = array('text'=>$_POST['text'], 'html'=>false);
				}
												
				$contributor = $this->_createOrFindContributor();
				Zend_Registry::set('contributor', $contributor);
				
				// If a particular contribution type implies (requires) a file
				// to be uploaded, add necessary options for insert_item().
				// FIXME: This wouldn't account for situations where uploads are
				// optional, such as documents.
				if ($this->_uploadedFileIsRequired($_POST['type'])) {
				    $fileUploadOptions = array(
                        'files'=>'contributed_file', // Form input name
                        'file_transfer_type'=>'Upload',
                        'file_ingest_options'=>array(
                            'ignoreNoFile'=> false));
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
				}
																								
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
	 * Determine whether or not we are requiring that a file be uploaded for
	 * a given Item Type.
	 * 
	 * Files are currently required for: Moving Image, Still Image, Sound.
	 * 
	 * @param string $itemTypeName
	 * @return boolean
	 **/
	protected function _uploadedFileIsRequired($itemTypeName)
	{
	    $fileRequiredItemTypes = array('Moving Image', 'Still Image', 'Sound');
	    return in_array($itemTypeName, $fileRequiredItemTypes, true);
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
	 * @internal This is copied almost exactly from the SimpleContactForm.
	 * 
	 * @return Zend_Captcha_Recaptcha|null
	 **/
	protected function _setupCaptcha()
	{
	    $publicKey = get_option('contribution_recaptcha_public_key');
	    $privateKey = get_option('contribution_recaptcha_private_key');

	    if (empty($publicKey) or empty($privateKey)) {
	       return;
	    }
	    
        // Originating request:
        $captcha = new Zend_Captcha_ReCaptcha(array(
            'pubKey'=>$publicKey, 
            'privKey'=>$privateKey));

        return $captcha;
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
	 * Retrieve the form partial to display on the Contribution form for a 
	 * specific Item Type.   
	 * 
	 * The following Item Types will display a file form input and an optional
	 * 'Description' input: Still Image, Moving Image, Sound
	 * 
	 * All other Item Types will display as a textarea labeled 'Your Story'.
	 * 
	 * TODO: This should be extensible so that new forms can be written for 
	 * custom Item Types.
	 * @return void
	 **/
	public function partialAction() 
	{
		$contributionType = $this->_getParam('contributiontype');
		if (($text = $this->_getParam('text')) or ($text = $this->_getParam('description'))) {
		    $this->view->text = $text;
		}
		switch ($contributionType) {
		    // Display a file input for uploading a single file to Omeka.
			case 'Still Image':
			case 'Moving Image':
			case 'Sound':
				$partial = "-file";
				break;
			// Document and everything else will display as a textarea
			default:
				$partial = "-document";
				break;
		}
		$this->render($partial);
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
	protected function _sendEmailNotification($email, $item)
	{
		$from_email = get_option('contribution_notification_email');
		
		//If this field is empty, don't send the email
		if(empty($from_email)) {
			return;
		}
		
		$this->view->item = $item;
		$item->view->email = $email;
				
		$mail = new Zend_Mail();
        $mail->setBodyText($this->view->render('index/email.php'));
        $mail->setFrom($from_email, get_option('site_title') . ' Administrator');
        $mail->addTo($email);
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