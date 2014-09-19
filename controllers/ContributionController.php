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
class Contribution_ContributionController extends Omeka_Controller_AbstractActionController
{
    protected $_autoCsrfProtection = true;

    protected $_captcha;

    protected $_profile;

    public function init()
    {
        $this->_helper->db->setDefaultModelName('ContributionContributedItem');
    }

    /**
     * Index action; simply forwards to contributeAction.
     */
    public function indexAction()
    {
        $this->_forward('contribute');
    }

    public function myContributionsAction()
    {
        $user = current_user();
        if (empty($user)) {
            $this->_helper->redirector('login', 'users', 'default');
        }

        $contribItemTable = $this->_helper->db->getTable('ContributionContributedItem');

        $contribItems = array();
        if(!empty($_POST)) {
            foreach($_POST['contribution_public'] as $id=>$value) {
                $contribItem = $contribItemTable->find($id);
                $contribItem->public = (integer) $value;
                $contribItem->anonymous = (integer) $_POST['contribution_anonymous'][$id];
                $contribItem->deleted = (integer) $_POST['contribution_deleted'][$id];

                if($contribItem->save()) {
                    $this->_helper->flashMessenger( __('Your contributions have been updated.'), 'success');
                } else {
                    $this->_helper->flashMessenger($contribItem->getErrors());
                }

                // Clean list for next view.
                if (!$contribItem->deleted) {
                    $contribItems[] = $contribItem;
                }
            }
        } else {
            $contribItems = $contribItemTable->findBy(array(
                'contributor' => $user->id,
                'deleted' => false,
            ));
        }
        $this->view->contrib_items = $contribItems;
    }

    /**
     * Action for main contribution form.
     */
    public function contributeAction()
    {
        $this->_captcha = $this->_setupCaptcha();
        $csrf = new Omeka_Form_SessionCsrf;
        $this->view->csrf = $csrf;
        if(!empty($_POST)) {
            $defaultType = get_option('contribution_default_type');
            if (!$csrf->isValid($_POST)) {
                $this->_helper->_flashMessenger(__('There was an error on the form. Please try again.'), 'error');
                $typeId = null;
                if (isset($_POST['contribution_type']) && ($postedType = $_POST['contribution_type'])) {
                    $typeId = $postedType;
                } elseif ($defaultType) {
                    $typeId = $defaultType;
                }
                $this->_setupContributeSubmit($typeId);
                return;
            }
            if ($this->_processForm($_POST)) {
                $route = $this->getFrontController()->getRouter()->getCurrentRouteName();
                $this->_helper->_redirector->gotoRoute(array('action' => 'thankyou'), $route);
            } else {
                $typeId = null;
                if (isset($_POST['contribution_type']) && ($postedType = $_POST['contribution_type'])) {
                    $typeId = $postedType;
                } elseif ($defaultType) {
                    $typeId = $defaultType;
                }
                if ($this->_captcha) {
                    $this->view->captchaScript = $this->_captcha->render(new Zend_View);
                }
                $this->_setupContributeSubmit($typeId);

                if(isset($this->_profile) && !$this->_profile->exists()) {
                    $this->_helper->flashMessenger($this->_profile->getErrors(), 'error');
                    return;
                }
            }
        } else {
            if($this->_captcha) {
                $this->view->captchaScript = $this->_captcha->render(new Zend_View);
            }
            $defaultType = get_option('contribution_default_type');
            $this->_setupContributeSubmit($defaultType);
        }
    }

    /**
     * Action for main contribution edit form.
     *
     * Current differences with "add contribution": The type cannot be changed;
     * no captcha; old images are displayed and not removable.
     */
    public function editAction()
    {
        // Check contribution (else keep error 404?).
        $contributedItem = $this->_helper->db->findById();
        if (empty($contributedItem) || $contributedItem->deleted) {
            $this->_helper->_flashMessenger(__("This contribution doesn't exist."), 'error');
            return current_user()
                ? $this->_helper->redirector('my-contributions')
                : $this->_helper->redirector('index', 'index', 'default');
        }

        // Check rights of the user on this contribution (owner of the item).
        $contributor = $contributedItem->getContributor();
        $user = $this->getCurrentUser();
        $item = $contributedItem->Item;
        if (!$user || $user->id !== $contributor->id || $item->owner_id !== $user->id) {
            $this->_helper->_flashMessenger(__('You are not the contributor of this item.'), 'error');
            return $item->public
                ? $this->_helper->redirector('show', 'items', 'default', array('id' => $item->id))
                : $this->_helper->redirector('index', 'index', 'default');
        }

        // Prepare next view (TODO Set it in another method to avoid preparation if not needed?)
        $csrf = new Omeka_Form_SessionCsrf;
        $this->view->csrf = $csrf;
        $this->view->contribution_contributed_item = $contributedItem;
        $this->view->item = $item;
        $contributionType = $this->_helper->db->getTable('ContributionType')->getByItemType($item->item_type_id);
        $this->_setupContributionSubmit($contributionType);

        if (!$this->getRequest()->isPost()) {
            return;
        }

        if (!$csrf->isValid($_POST)) {
            $this->_helper->_flashMessenger(__('There was an error on the form. Please try again.'), 'error');
            return;
        }

        if (!$this->_validateContribution($_POST)) {
            return;
        }

        // Because there is already one attached file when item is created,
        // no new check is done on required file.

        // If not simple and the profile doesn't process, send back false for the
        // error. This should be done before processing item in order to set
        // profile type and to avoid settings profile elements to item.
        $this->_processUserProfile($user, $_POST);

        // Use a specific setPostData() for item, because some post fields are
        // not set in the post. Metadata are not changed via contribution
        // form.
        $this->_setPostData($item, $_POST);

        // Tags are added separately.
        if ($contributionType->add_tags && isset($_POST['tags'])) {
            $item->addTags($_POST['tags']);
        }

        // Allow plugins to deal with the inputs they may have added to the form
        // and that are not managed via hooks before or after save item.
        fire_plugin_hook(
            'contribution_save_form',
            array(
                'contributionType' => $contributionType,
                'record' => $item,
                'post' => $_POST,
        ));

        // Everything has been checked, so save item.
        if ($item->save(false)) {
            $successMessage = $this->_getEditSuccessMessage($contributedItem);
            $this->_helper->flashMessenger($successMessage, 'success');

            // Update contribution before redirect.
            $contributedItem->public = (integer) $_POST['contribution-public'];
            if (!$contributedItem->public) {
                $contributedItem->makeNotPublic();
            }
            $contributedItem->anonymous = (integer) $_POST['contribution-anonymous'];
            $contributedItem->deleted = 0;
            $contributedItem->save();

            $this->_redirectAfterEdit($contributedItem);
        }
        // Error during saving.
        else {
            $this->_helper->flashMessenger($item->getErrors());
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
     * @param ContributionType|int $contributionType ContributionType id
     */
    protected function _setupContributeSubmit($contributionType)
    {
        $this->view->item = new Item;
        $this->_setupContributionSubmit($contributionType);
    }

    /**
     * Common tasks whenever displaying submit form for contribution or edition.
     *
     * @param ContributionType|int $contributionType ContributionType or id.
     */
    protected function _setupContributionSubmit($contributionType)
    {
        // Override default element form display
        $this->view->addHelperPath(CONTRIBUTION_HELPERS_DIR, 'Contribution_View_Helper');

        if (!is_object($contributionType)) {
            $contributionType = get_db()->getTable('ContributionType')->find($contributionType);
        }
        $this->view->type = $contributionType;

        // Setup profile stuff, if needed.
        $profileTypeId = get_option('contribution_user_profile_type');
        if(plugin_is_active('UserProfiles') && $profileTypeId) {
            $this->view->addHelperPath(USER_PROFILES_DIR . '/helpers', 'UserProfiles_View_Helper_');
            $profileType = $this->_helper->db->getTable('UserProfilesType')->find($profileTypeId);
            $this->view->profileType = $profileType;

            $user = current_user();
            if($user) {
                $profile = $this->_helper->db->getTable('UserProfilesProfile')->findByUserIdAndTypeId($user->id, $profileTypeId);
            }
            if(empty($profile)) {
                $profile = new UserProfilesProfile();
                $profile->type_id = $profileTypeId;
            }
            $this->view->profile = $profile;
        }
    }

    /**
     * Creates the reCAPTCHA object and returns it.
     *
     * @return Zend_Captcha_Recaptcha|null
     */
    protected function _setupCaptcha()
    {
        if(current_user()) {
            return false;
        } else {
            return Omeka_Captcha::getCaptcha();
        }
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
            //for the "Simple" configuration, look for the user if exists by email. Log them in.
            //If not, create the user and log them in.
            $user = current_user();
            $open = get_option('contribution_open');
            if ( get_option('contribution_strict_anonymous') ) {
                $strictAnonymous = empty($post['contribution_email']);
            } else {
                $strictAnonymous = false;
            }
            

            if(!$user && $open && !$strictAnonymous) {
                $user = $this->_helper->db->getTable('User')->findByEmail($post['contribution_email']);
            }

            if (!$user && $strictAnonymous) {
                $user = $this->_createNewAnonymousUser();
            }
            // if still not a user, need to create one based on the email address
            if(!$user) {
                $user = $this->_createNewGuestUser($post);
                if($user->hasErrors()) {
                    $errors = $user->getErrors()->get();
                    //since we're creating the user behind the scenes, skip username and name errors
                    unset($errors['name']);
                    unset($errors['username']);
                    foreach($errors as $error) {
                        $this->_helper->flashMessenger($error, 'error');
                    }
                    return false;
                }
            }

            if (!$this->_validateContribution($post)) {
                return false;
            }

            $contributionTypeId = (integer) $post['contribution_type'];
            if (!empty($contributionTypeId)) {
                $contributionType = get_db()->getTable('ContributionType')->find($contributionTypeId);
                $itemTypeId = $contributionType->getItemType()->id;
            } else {
            	$this->_helper->flashMessenger(__('You must select a type for your contribution.'), 'error');
                return false;
            }
            // Public is updated with the contributed item.
            $itemMetadata = array('public'       => false,
                                  'featured'     => false,
                                  'item_type_id' => $itemTypeId);

            $collectionId = (integer) get_option('contribution_collection_id');
            if (!empty($collectionId)) {
                $itemMetadata['collection_id'] = $collectionId;
            }

            // TODO Check if there is at least one file if one file or more is required and remove the catch below.
            $fileMetadata = $this->_prepareFilesUpload($contributionType);

            // This is a hack to allow the file upload job to succeed
            // even with the synchronous job dispatcher.
            $acl = get_acl();
            if ($acl) {
                $acl->allow(null, 'Items', 'showNotPublic');
                $acl->allow(null, 'Collections', 'showNotPublic');
            }
            try {
                //in case we're doing Simple, create and save the Item so the owner is set, then update with the data
                $item = new Item();
                $item->setOwner($user);
                $item->save();
                $item = update_item($item, $itemMetadata, array(), $fileMetadata);
            } catch(Omeka_Validate_Exception $e) {
                $this->flashValidatonErrors($e);
                $item->delete();
                return false;
            } catch (Omeka_File_Ingest_InvalidException $e) {
                // Copying this cruddy hack
                if (strstr($e->getMessage(), "'file'")) {
                   $this->_helper->flashMessenger("You must upload a file when making a {$contributionType->display_name} contribution.", 'error');
                }
                // Check multiple files.
                elseif (strstr($e->getMessage(), "file_")) {
                    $this->_helper->flashMessenger(__('One or more files have not been uploaded.')
                        . ' ' . __('You must upload a file when making a %s contribution.', $contributionType->display_name), 'error');
                } else {
                    $this->_helper->flashMessenger($e->getMessage());
                }
                $item->delete();
                return false;
            } catch (Exception $e) {
                $this->_helper->flashMessenger($e->getMessage());
                $item->delete();
                return false;
            }

            $this->_addElementTextsToItem($item, $post['Elements']);

            // Tags are added separately.
            if ($contributionType->add_tags && isset($post['tags'])) {
                $item->addTags($post['tags']);
            }
            // Allow plugins to deal with the inputs they may have added to the form
            // and that are not managed via hooks before or after save item.
            fire_plugin_hook('contribution_save_form', array('contributionType'=>$contributionType,'record'=>$item, 'post'=>$post));

            $item->save();
            //if not simple and the profile doesn't process, send back false for the error
            $this->_processUserProfile($user, $post);
            $this->_linkItemToContributedItem($item, $post);
            $this->_sendEmailNotifications($user, $item);
            return true;
        }
        return false;
    }

    protected function _processUserProfile($user, $post)
    {
        $profile = $this->_getProfileForUser($user);
        // Check if there is a profile.
        if (!$profile) {
            return true;
        }
        $profile->setPostData($post);
        return $profile->save(false);
    }

    /**
     * Get the user profile if the plugin User Profile is used and active. The
     * profile is a new one, for the current user, if it is not set.
     *
     * @param User $user Current user if null.
     * @return UserProfilesProfile|false. False if the plugin is not used.
     */
    protected function _getProfileForUser($user = null)
    {
        if (is_null($this->_profile)) {
            $profileTypeId = get_option('contribution_user_profile_type');
            if ($profileTypeId && plugin_is_active('UserProfiles')) {
                if (is_null($user)) {
                    $user = current_user();
                }
                $profile = $this->_helper->db->getTable('UserProfilesProfile')->findByUserIdAndTypeId($user->id, $profileTypeId);
                if (!$profile) {
                    $profile = new UserProfilesProfile;
                    $profile->setOwner($user);
                    $profile->type_id = $profileTypeId;
                    $profile->public = 0;
                    $profile->setRelationData(array('subject_id' => $user->id, 'user_id' => $user->id));
                }
                $this->_profile = $profile;
            }
            // The plugin is not used.
            else {
                $this->_profile = false;
            }
        }
        return $this->_profile;
    }

    /**
     * Deals with files specified on the contribution form.
     *
     * @todo Check if multiple files are allowed.
     * @todo Error when option is required and when multiple files are allowed: an empty contributed_file input generate an error.
     *
     * @param ContributionType $contributionType Type of contribution.
     * @return array Files upload array.
     */
    protected function _prepareFilesUpload($contributionType)
    {
        if ($contributionType->isFileAllowed()) {
            $options = array();
            $options['ignoreNoFile'] = !$contributionType->isFileRequired();

            $fileMetadata = array(
                'file_transfer_type' => 'Upload',
                'files' => 'file',
                'file_ingest_options' => $options
            );

            // Add the whitelists for uploaded files
            $fileValidation = new ContributionFileValidation;
            $fileValidation->enableFilter();

            return $fileMetadata;
        }
        return array();
    }

    protected function _linkItemToContributedItem($item, $post)
    {
        $linkage = new ContributionContributedItem;
        $linkage->item_id = $item->id;
        $linkage->public = (integer) $post['contribution-public'];
        $linkage->anonymous = (integer) $post['contribution-anonymous'];
        $linkage->deleted = 0;
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
        $db = get_db();
        $elementTable = $db->getTable('Element');
        $sql = "SELECT DISTINCT `id` from `$db->ElementSet` WHERE `record_type` = 'UserProfilesType' ";
        $userProfilesElementSets = $db->fetchCol($sql);
        foreach($elements as $elementId => $elementTexts) {
            $element = $elementTable->find($elementId);
            $elSet = $element->getElementSet();
            //need to skip over elements that are intended for a User Profile, not the item
            if (in_array($elSet->id, $userProfilesElementSets)) {
                continue;
            }
            foreach($elementTexts as $elementText) {
                if (!empty($elementText['text'])) {
                    $item->addTextForElement($element, $elementText['text']);
                }
            }
        }
    }

    /**
     * Set post data to an item.
     *
     * All data in the post are set: elements, basic metadata (item_type_id,
     * collection_id, featured, public, owner_id), and other data attached to an
     * item, like geolocation.
     *
     * @todo Use builder?
     *
     * @param Item $item Item to add texts to.
     * @param array $post Array of element inputs from form.
     * @param array $itemMetadata Array of basic data of item (public...).
     * @return boolean True if success, false else.
     */
    protected function _setPostData($item, $post, $itemMetadata = array())
    {
        // Check if there is a profile in order to remove elements data used for
        // profile because they are related to profile, not to item.
        $profile = $this->_getProfileForUser();
        if ($profile) {
            $profileElements = $profile->getAllElements();
            foreach ($profileElements as $key => $value) {
                foreach ($value as $element) {
                    unset($post['Elements'][$element->id]);
                }
            }
        }

        if (!isset($post['Elements'])) {
            $post['Elements'] = array();
        }

        // Overwrite post data with internal itemMetadata.
        if ($itemMetadata) {
            $post += $itemMetadata;
        }
        // Else reset metadata to keep clean current ones (avoid checking post).
        else {
            unset($post['item_type_id']);
            unset($post['collection_id']);
            unset($post['featured']);
            unset($post['public']);
            unset($post['owner_id']);
        }

        // If ReplaceElementTexts() is true, all element texts are removed.
        // If an element is set, it replaces all fields of the element.
        // If an element is not set, it is removed.
        // If a non element is not set, it is not changed.
        // So, to get all element texts is needed to update a record.

        // Get and format current metadata.
        $currentElements = array();
        foreach ($item->getAllElementTexts() as $elementText) {
            $currentElements[$elementText->element_id] = array(
                array(
                    'text' => $elementText->text,
                    'html' => $elementText->html,
                ),
            );
        }

        // This is not a merge, but an update.
        $post['Elements'] += $currentElements;

        $item->setPostData($post);
    }

    /**
     * Validate the contribution form submission.
     *
     * Will flash validation errors that occur.
     *
     * Verify the validity of the following form elements:
     *      Terms agreement
     *
     * @return bool
     */
    protected function _validateContribution($post)
    {
        if (empty($post)) {
            return false;
        }

        // The final form submit was not pressed.
        if (!isset($post['form-submit'])) {
            $this->_helper->flashMessenger(__('You should press submit button.'), 'error');
            return false;
        }

        if ($post['terms-agree'] == 0) {
            $this->_helper->flashMessenger(__('You must agree to the Terms and Conditions.'), 'error');
            return false;
        }

        // ReCaptcha ignores the first argument.
        if ($this->_captcha and !$this->_captcha->isValid(null, $post)) {
            $this->_helper->flashMessenger(__('Your CAPTCHA submission was invalid, please try again.'), 'error');
            return false;
        }

        return true;
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
     */
    protected function _sendEmailNotifications($recipient, $item)
    {
        $fromAddress = get_option('contribution_email_sender');
        $siteTitle = get_option('site_title');

        $this->view->item = $item;

        //If this field is empty, don't send the email
        if (!empty($fromAddress)) {
            $contributorMail = new Zend_Mail('UTF-8');
            $body .= get_option('contribution_email');
            $url = record_url($item, 'show', true);
            $link = "<a href='$url'>$url</a>";
            $body .= "<p>" . __("Contribution URL (pending review by project staff): ") . $link .  "</p>";
            $body .= "<p>" . __("Your username is %s", $recipient->username) . "</p>";
            $passwordRecoveryUrl = WEB_ROOT . "/users/forgot-password";
            $passwordRecoveryLink = "<a href='$passwordRecoveryUrl'>$passwordRecoveryUrl</a>";
            $body .= "<p>" . __("To log in and change your username, request a password here: ") . $passwordRecoveryLink . "<p>";
            $contributorMail->setBodyHtml($body);
            $contributorMail->setFrom($fromAddress, __("%s Administrator", $siteTitle ));
            $contributorMail->addTo($recipient->email);
            $contributorMail->setSubject(__("Your %s Contribution", $siteTitle));
            $contributorMail->addHeader('X-Mailer', 'PHP/' . phpversion());
            try {
                $contributorMail->send();
            } catch (Zend_Mail_Exception $e) {
                _log($e);
            }
        }

        //notify admins who want notification
        $toAddresses = explode(",", get_option('contribution_email_recipients'));
        $fromAddress = get_option('administrator_email');

        foreach ($toAddresses as $toAddress) {
            if (empty($toAddress)) {
                continue;
            }
            $adminMail = new Zend_Mail('UTF-8');
            $body = "<p>";
            $body .= __("A new contribution to %s has been made.", get_option('site_title'));
            $body .= "</p>";
            set_theme_base_url('admin');
            $url = record_url($item, 'show', true);
            $link = "<a href='$url'>$url</a>";
            $body .= "<p>" . __("Contribution URL for review: ") . $link .  "</p>";

            revert_theme_base_url();
            $adminMail->setBodyHtml($body);
            $adminMail->setFrom($fromAddress, "$siteTitle");
            $adminMail->addTo($toAddress);
            $adminMail->setSubject(__("New %s Contribution", $siteTitle ));
            $adminMail->addHeader('X-Mailer', 'PHP/' . phpversion());
            try {
                $adminMail->send();
            } catch (Zend_Mail_Exception $e) {
                _log($e);
            }
        }
    }

    
    
    
    protected function _createNewGuestUser($post)
    {
        $user = new User();
        $email = $post['contribution_email'];
        $split = explode('@', $email);
        $name = $split[0];
        if(version_compare(OMEKA_VERSION, '2.2-dev', '<')) {
            $username = str_replace('@', 'AT', $email);
            $username = str_replace('.', 'DOT', $username);
            $user->username = $username;
        } else {
            $user->username = $email;
        }
        $user->email = $email;
        $user->name = $name;
        $user->role = 'guest';
        $user->active = 1;
        try {
            $user->save();
            //activate user so they can retrieve password without admin needing to activate first
            $activation = UsersActivations::factory($user);
            $activation->save();
        } catch(Exception $e) {
            _log($e);
        }
        return $user;
    }
    
    protected function _createNewAnonymousUser()
    {
        $user = new User();
        $userTable = $this->_helper->db->getTable('User');
        $anonymousCount = $userTable->count(array('role' => 'contribution_anonymous'));
        $domain = $_SERVER['HTTP_HOST'];
        if ($domain == 'localhost') {
            $domain = 'localhost.info';
        }
        $email = "anonymous" . $anonymousCount . "@" . $domain;
        $split = explode('@', $email);
        $name = $split[0];
        if(version_compare(OMEKA_VERSION, '2.2-dev', '<')) {
            $username = str_replace('@', 'AT', $email);
            $username = str_replace('.', 'DOT', $username);
            $user->username = $username;
        } else {
            $user->username = $email;
        }
        $user->email = $email;
        $user->name = $name;
        $user->role = 'contribution_anonymous';
        $user->active = 0;
        $user->save();
        return $user;
    }

    /**
     * Return the success message for editing a record.
     *
     * @param Omeka_Record_AbstractRecord $record
     * @return string
     */
    protected function _getEditSuccessMessage($record)
    {
        $itemTitle = $this->_getElementMetadata($record->Item, 'Dublin Core', 'Title');
        if ($itemTitle != '') {
            return __('The contributed item "%s" was successfully updated!', $itemTitle);
        } else {
            return __('The contributed item #%s was successfully updated!', strval($item->id));
        }
    }

    protected function _getElementMetadata($record, $elementSetName, $elementName)
    {
        $m = new Omeka_View_Helper_Metadata;
        return strip_formatting($m->metadata($record, array($elementSetName, $elementName)));
    }

    /**
     * Redirect to items/show after a contribution is successfully edited.
     *
     * The default is to redirect to this record's show page.
     *
     * @param Omeka_Record_AbstractRecord $record
     */
    protected function _redirectAfterEdit($record)
    {
        $this->_helper->redirector('show', 'items', 'default', array('id' => $record->item_id));
    }
}
