<?php 
require_once 'Contributor.php';

class Contribution_IndexController extends Omeka_Controller_Action
{	
    protected $_captcha;
    
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
		
		$this->_captcha = $this->_setupCaptcha();
		
		if($this->processForm($item))
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
	
	public function browseAction()
	{
	    $this->_setParam('per_page', CONTRIBUTORS_PER_PAGE);

	    $criteria = $this->_getAllParams();

	    $contributors = $this->getTable('Contributor')->findBy($criteria);
	    $totalContributors = $this->getTable('Contributor')->count($criteria);

	    Zend_Registry::set('pagination', array(
	        'page'=>$this->_getParam('page', 1),
	        'total_results'=>$totalContributors,
	        'per_page'=>$this->_getParam('per_page')));

	    $this->view->contributors = $contributors;
	    $this->view->totalContributors = $totalContributors;
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
     * @internal TODO This mostly duplicates Item::_saveFiles().
     * 
     * @param Item
     * @return File|false Returns false if the upload failed.
     **/
    protected function _uploadFileToItem($item)
    {   
        try {
            $file = new File();
            // The key is 0 since that is the first index for the uploaded $_FILES array.
            $file->upload('file', 0);
            $file->item_id = $item->id;
            $file->forceSave();
        } catch (Omeka_Upload_Exception $e) {
            if (!$file->exists()) {
                $file->unlinkFile();
            }
            $this->flashError($e->getMessage());
            return false;
        }
        
        // Should this hook be fired?  Do other plugins need to hook into the
        // contribution form?  What about the Geolocation plugin?
        fire_plugin_hook('after_upload_file', $file, $item);
        
        return $file;
    }
    
    /**
     * Preconditions: This runs assuming that a user is uploading a file.
     * 
     * @return boolean
     **/
    protected function _fileUploadIsValid()
    {
        $isValid = true;
        
        // So, due to quirks with the way File::handleUploadErrors() works, we need
        // $_FILES['file'] to be a multi-dimensional array even though there can
        // only be one file upload at a time.
        
        // If attempting to upload more than one file.
        if (count($_FILES['file']['name']) > 1) {
            $this->flashError("File upload error: Not allowed to upload more than one file through the Contribution form!");
            $isValid = false;
        }
        
        try {
            File::handleUploadErrors('file');
        } catch (Omeka_Upload_Exception $e) {
            $this->flashError("File upload error: " . $e->getMessage());
            $isValid = false;
        }
        
        return $isValid;
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
		    
		    // ReCaptcha ignores the first argument.
		    if ($this->_captcha and !$this->_captcha->isValid(null, $_POST)) {
                $this->flashError('Your CAPTCHA submission was invalid, please try again.');
                return false;
		    }
		    
			if(array_key_exists('pick_type', $_POST)) return false;
			
            // Documents do not contain files to be uploaded (does this even make
            // sense? - what about PDFs/text files?)
			$isFileUpload = !empty($_FILES["file"]['name'][0]) and 
			    ($_POST['type'] != 'Document');
			
			if ($isFileUpload and !$this->_fileUploadIsValid()) {
			    return false;
			}
			
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
				
				$creatorName = $_POST['contributor_is_creator'] ? $contributorName : (string)$_POST['creator'];
				
				if (empty($creatorName)) {
				    throw new Omeka_Validator_Exception('Creator: Please provide a valid name for the creator.');
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
				    if (empty($_POST['text'])) {
				        throw new Omeka_Validator_Exception('Story: Please provide the text of the story.');
				    }
				}
												
				$contributor = $this->createOrFindContributor();
				
				// Needed to tag the items properly.
				$itemMetadata['tag_entity'] = $contributor->Entity;					
				$item = insert_item($itemMetadata, $elementTexts);
				
				if ($isFileUpload) {
				    if (!$this->_uploadFileToItem($item)) {
				        return false;
				    }
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
		if (($text = $this->_getParam('text')) or ($text = $this->_getParam('description'))) {
		    $this->view->text = $text;
		}
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