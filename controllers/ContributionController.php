<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */
 
/**
 * Controller for contributions themselves.
 */
class Contribution_ContributionController extends Omeka_Controller_Action
{   
    protected $_captcha;
    
    /**
     * Index action; simply forwards to contributeAction.
     */
    public function indexAction()
    {
        $this->_forward('contribute');
    }
    
    /**
     * Action for main contribution form.
     */
    public function contributeAction()
    {
        $this->_captcha = $this->_setupCaptcha();
        
        if ($this->_processForm($_POST)) {
            $route = $this->getFrontController()->getRouter()->getCurrentRouteName();
            $this->redirect->gotoRoute(array('action' => 'thankyou'), $route);
        } else {
            if ($this->_captcha) {
                $this->view->captchaScript = $this->_captcha->render(new Zend_View);
            }
            
            $typeId = null;
            if (isset($_POST['contribution_type']) && ($postedType = $_POST['contribution_type'])) {
                $typeId = $postedType;
            } else if ($defaultType = get_option('contribution_default_type')) {
                $typeId = $defaultType;
            }
            if ($typeId) {
                $this->_setupContributeSubmit($typeId);
                $this->view->typeForm = $this->view->render('contribution/type-form.php');
            }
        }
    }
    
    /**
     * Action for AJAX request from contribute form.
     */
    public function typeFormAction()
    {
        $this->_setupContributeSubmit($_POST['contribution_type']);
    }
    
    /**
     * Displays terms of service for contribution.
     */
    public function termsAction()
    {
    }
    
    /**
     * Displays a "Thank You" message to users who have contributed an item 
     * through the public form.
     */
    public function thankyouAction()
    {
    }
    
    /**
     * Common tasks whenever displaying submit form for contribution.
     *
     * @param int $typeId ContributionType id
     */
    public function _setupContributeSubmit($typeId)
    {
        // Override default element form display        
        $this->view->addHelperPath(CONTRIBUTION_HELPERS_DIR, 'Contribution_View_Helper');
        $item = new Item;
        $this->view->item = $item;
        
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
        return Omeka_Captcha::getCaptcha();
    }
    
    /**
     * Handle the POST for adding an item via the public form.
     * 
     * Validate and save the contribution to the database.  Save the ID of the
     * new item to the session.  Redirect to the consent form. 
     * 
     * If validation fails, render the Contribution form again with errors.
     *
     * @param array $post POST array
     * @return bool
     */
    protected function _processForm($post)
    {       
        if (!empty($post)) {
            // The final form submit was not pressed.
            if (!isset($post['form-submit'])) {
                return false;
            }
            
            if (!$this->_validateContribution($post)) {
                return false;
            }
            
            $contributionTypeId = trim($post['contribution_type']);
            if ($contributionTypeId !== "" && is_numeric($contributionTypeId)) {
                $contributionType = get_db()->getTable('ContributionType')->find($contributionTypeId);
                $itemTypeId = $contributionType->getItemType()->id;
            } else {
                $this->flashError('You must select a type for your contribution.');
                return false;
            }

            if (!($contributor = $this->_processContributor($item, $post))) {
                return false;
            }
            
            $itemMetadata = array('public'       => false,
                                  'featured'     => false,
                                  'item_type_id' => $itemTypeId);
            
            $collectionId = get_option('contribution_collection_id');
            if (!empty($collectionId) && is_numeric($collectionId)) {
                $itemMetadata['collection_id'] = (int) $collectionId;
            }
            
            $fileMetadata = $this->_processFileUpload($contributionType);

            // This is a hack to allow the file upload job to succeed
            // even with the synchronous job dispatcher.
            if ($acl = get_acl()) {
                $acl->allow(null, 'Items', 'showNotPublic');
            }

            try {
                $item = insert_item($itemMetadata, array(), $fileMetadata);
            } catch(Omeka_Validator_Exception $e) {
                $this->flashValidatonErrors($e);
                return false;
            } catch (Omeka_File_Ingest_InvalidException $e) {
                // Copying this cruddy hack
                if (strstr($e->getMessage(), "The file 'contributed_file' was not uploaded")) {
                   $this->flashError("You must upload a file when making a {$contributionType->display_name} contribution.");
                } else {
                    $this->flashError($e->getMessage());
                }
                return false;
            } catch (Exception $e) {
                $this->flashError($e->getMessage());
                return false;
            }

            $this->_addElementTextsToItem($item, $post['Elements']);
            // Allow plugins to deal with the inputs they may have added to the form.
            fire_plugin_hook('contribution_save_form', $contributionType, $item, $post);
            $item->save();

            $this->_linkItemToContributor($item, $contributor, $post);

            $this->_sendEmailNotification($contributor->email, $item);
            
            return true;
        }
        return false;
    }
    
    /**
     * Deals with files specified on the contribution form.
     *
     * @param ContributionType $contributionType Type of contribution.
     * @return array File upload array.
     */
    protected function _processFileUpload($contributionType) {
        if ($contributionType->isFileAllowed()) {
            $options = array();
            if ($contributionType->isFileRequired()) {
                $options['ignoreNoFile'] = false;
            } else {
                $options['ignoreNoFile'] = true;
            }

            $fileMetadata = array(
                'file_transfer_type' => 'Upload',
                'files' => 'contributed_file',
                'file_ingest_options' => $options
            );

            // Add the whitelists for uploaded files
            $fileValidation = new ContributionFileValidation;
            $fileValidation->enableFilter();

            return $fileMetadata;
        }
        return array();
    }

    /**
     * Deals with metadata about the item's contributor.
     *
     * @param Item $item Contributed item.
     * @param array $post POST array.
     */
    protected function _processContributor($item, $post)
    {
        $table = get_db()->getTable('ContributionContributor');
        $email = $post['contributor-email'];
        $name = $post['contributor-name'];
        $ip = $this->getRequest()->getClientIp();

        if (!($contributor = $table->findUnique($email, $name))) {
            $contributor = new ContributionContributor;
            $contributor->email = $email;
            $contributor->name = $name;
        }
        $contributor->setDottedIpAddress($ip);
        try {
            $contributor->forceSave();

            $contributorMetadata = $post['ContributorFields'];
            if(is_array($contributorMetadata)) {
                foreach ($contributorMetadata as $fieldId => $value) {
                    $valueModel = new ContributionContributorValue;
                    $valueModel->field_id = $fieldId;
                    $valueModel->contributor_id = $contributor->id;
                    $valueModel->value = $value;
                    $valueModel->save();
                }
            }

            return $contributor;
        } catch (Omeka_Validator_Exception $e) {
            $this->flashValidationErrors($e);
            return false;
        }
    }

    protected function _linkItemToContributor($item, $contributor, $post)
    {
        $linkage = new ContributionContributedItem;
        $linkage->contributor_id = $contributor->id;
        $linkage->item_id = $item->id;
        $linkage->public = $post['contribution-public'];
        $linkage->save();
    }
    
    /**
     * Adds ElementTexts to item.
     *
     * @param Item $item Item to add texts to.
     * @param array $elements Array of element inputs from form
     */
    protected function _addElementTextsToItem($item, $elements)
    {
        $elementTable = get_db()->getTable('Element');
        foreach($elements as $elementId => $elementTexts) {
            $element = $elementTable->find($elementId);
            foreach($elementTexts as $elementText) {
                if (!empty($elementText['text'])) {
                    $item->addTextForElement($element, $elementText['text']);
                }
            }
        }
    }
    
    /**
     * Validate the contribution form submission.
     * 
     * Will flash validation errors that occur.
     * 
     * Verify the validity of the following form elements:
     *      Captcha (if set up)
     *      Terms agreement
     *      
     * @return bool
     */
    protected function _validateContribution($post)
    {
        $isValid = true;
        
        $errors = array();

        // ReCaptcha ignores the first argument.
        if ($this->_captcha and !$this->_captcha->isValid(null, $_POST)) {
            $errors[] = 'Your CAPTCHA submission was invalid, please try again.';
            $isValid = false;
        }
        
        if (!@$post['terms-agree']) {
            $errors[] = 'You must agree to the Terms and Conditions.';
            $isValid = false;
        }
        
        if ($errors) {
            $this->flashError(join("\n", $errors));
        }
        
        return $isValid;
    }
    
    /**
     * Send an email notification to the user who contributed the Item.
     * 
     * This email will appear to have been sent from the address specified via
     * the 'contribution_email_sender' option.
     * 
     * @param string $email Address to send to.
     * @param Item $item Item that was contributed via the form.
     * @return void
     * @todo Update for new Contribution
     */
    protected function _sendEmailNotification($toEmail, $item)
    {
        $fromAddress = get_option('contribution_email_sender');
        $siteTitle = get_option('site_title');

        $this->view->item = $item;
        $item->view->email = $toEmail;
        
        //If this field is empty, don't send the email
        if (!empty($fromAddress)) {
            $contributorMail = new Zend_Mail;
            $contributorMail->setBodyText($this->view->render('contribution/contributor-email.php'));
            $contributorMail->setFrom($fromAddress, "$siteTitle Administrator");
            $contributorMail->addTo($toEmail);
            $contributorMail->setSubject("Your $siteTitle Contribution");
            $contributorMail->addHeader('X-Mailer', 'PHP/' . phpversion());
            try {
                $contributorMail->send();
            } catch (Zend_Mail_Exception $e) {
                debug($e);
            }
        }

        $fromAddress = get_option('administrator_email');
        $toAddresses = explode("\n", get_option('contribution_email_recipients'));

        foreach ($toAddresses as $toAddress) {
            if (empty($toAddress)) {
                continue;
            }
            $adminMail = new Zend_Mail;
            $adminMail->setBodyText($this->view->render('contribution/admin-email.php'));
            $adminMail->setFrom($fromAddress, "$siteTitle");
            $adminMail->addTo($toAddress);
            $adminMail->setSubject("New $siteTitle Contribution");
            $adminMail->addHeader('X-Mailer', 'PHP/' . phpversion());
            try {
                $adminMail->send();
            } catch (Zend_Mail_Exception $e) {
                debug($e);
            }
        }
    }
}
