<?php 
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */
 
/**
 * Controller for editing and viewing Contribution plugin settings.
 */
class Contribution_SettingsController extends Omeka_Controller_AbstractActionController
{
    /**
     * Index action; simply forwards to contributeAction.
     */
    public function indexAction()
    {
        $this->_redirect('contribution/settings/edit');
    }
    
    /**
     * Edit action
     */
    public function editAction()
    {
        require CONTRIBUTION_FORMS_DIR . DIRECTORY_SEPARATOR . 'Settings.php';
        //$form = new Contribution_Form_Settings;
        $form = $this->_getForm();
        $defaults = $this->_getOptions();
        $form->setDefaults($defaults);
        
        if (isset($_POST['contribution_settings_submit'])) {
            if ($form->isValid($_POST)) {
                $this->_setOptions($form->getValues());
                $this->flashSuccess('Settings have been saved.');
                // Do a POST/Redirect/GET pattern
                $this->_redirect($this->view->url(), array('prependBase' => false));
            } else {
                $this->flashError('There were errors found in your form. Please edit and resubmit.');
            }
        }
        
        $this->view->form = $form;
    }
    
    /**
     * Returns the options that are specified in the $_options property.
     *
     * @return array Array of option names.
     */
    private function _getOptions()
    {
        $options = array(
                'contribution_page_path',
                'contribution_email_sender',
                'contribution_email_recipients',
                'contribution_consent_text',
                'contribution_collection_id',
                'contribution_default_type');
        
        foreach ($options as $option) {
            $options[$option] = get_option($option);
        }
        return $options;
    }
    
    /**
     * Sets options that appear in both the form and $_options.
     *
     * @param array $newOptions array of $optionName => $optionValue.
     */
    private function _setOptions($newOptions)
    {
        foreach ($newOptions as $optionName => $optionValue) {
            if (in_array($optionName, ContributionPlugin::$options)) {
                set_option($optionName, $optionValue);
            }
        }
    }
    
    private function _getForm()
    {
        $form = new Omeka_Form_Admin(array('type'=>'contribution_settings'));
        
        $form->addElementToEditGroup('text', 'contribution_page_path', array(
            'label'       => 'Contribution Slug',
            'description' => 'Relative path from the Omeka root to the desired location for the contribution form. If left blank, the default path will be named &#8220;contribution.&#8221;',
            'filters'     => array(array('StringTrim', '/\\\s'))
        ));
        
        $form->addElementToEditGroup('text', 'contribution_email_sender', array(
            'label'       => 'Contribution Confirmation Email',
            'description' => 'An email message will be sent to each contributor from this address           confirming that they submitted a contribution to this website. Leave blank if you do not want an email sent.',
            'validators'  => array('EmailAddress')
        ));
        
        $form->addElementToEditGroup('textarea', 'contribution_email_recipients', array(
            'label'       => 'New Contribution Notification Emails',
            'description' => 'An email message will be sent to '
                           . 'each address here whenever a new item is '
                           . 'contributed. Leave blank if you do not want '
                           . 'anyone to be alerted of contributions by email.',
            'attribs'     => array('rows' => '5')
        ));
        
        $form->addElementToEditGroup('textarea', 'contribution_consent_text', array(
            'label'       => 'Text of Terms of Service',
            'description' => 'The text of the legal disclaimer to which contributors will agree.',
            'attribs'     => array('class' => 'html-editor', 'rows' => '15')
        ));
        
        $collections = get_db()->getTable('Collection')->findPairsForSelectForm();
        $collections = array('' => 'Do not put contributions in any collection') + $collections;        
        
        $form->addElementToEditGroup('select', 'contribution_collection_id', array(
            'label'        => 'Contribution Collection',
            'description'  => 'The collection to which contributions will be added. Changes here will only affect new contributions.',
            'multiOptions' => $collections
        ));
        
        $types = get_db()->getTable('ContributionType')->findPairsForSelectForm();
        $types = array('' => 'No default type') + $types;        
        
        $form->addElementToEditGroup('select', 'contribution_default_type', array(
            'label'        => 'Default Contribution Type',
            'description'  => 'The type that will be chosen for contributors by default.',
            'multiOptions' => $types
        ));
       return $form;
    }
}
