<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */
 
class Contribution_Form_Settings extends Omeka_Form
{
    public function init()
    {
        parent::init();
        
        $this->setMethod('post');
        $this->setAttrib('id', 'settings-form');

        $this->addElement('text', 'contribution_page_path', array(
            'label'       => 'Contribution Slug',
            'description' => 'Relative path from the Omeka root to the desired location for the contribution form. If left blank, the default path will be named &#8220;contribution.&#8221;',
            'filters'     => array(array('StringTrim', '/\\\s'))
        ));
        $this->addElement('text', 'contribution_email_sender', array(
            'label'       => 'Contribution Confirmation Email',
            'description' => 'An email message will be sent to each contributor from this address           confirming that they submitted a contribution to this website. Leave blank if you do not want an email sent.',
            'validators'  => array('EmailAddress')
        ));
        $this->addElement('textarea', 'contribution_email_recipients', array(
            'label'       => 'New Contribution Notification Emails',
            'description' => 'An email message will be sent to '
                           . 'each address here whenever a new item is '
                           . 'contributed. Leave blank if you do not want '
                           . 'anyone to be alerted of contributions by email.',
            'attribs'     => array('rows' => '5')
        ));
        $this->addElement('textarea', 'contribution_consent_text', array(
            'label'       => 'Text of Terms of Service',
            'description' => 'The text of the legal disclaimer to which contributors will agree.',
            'attribs'     => array('class' => 'html-editor', 'rows' => '15')
        ));
        $this->addElement('select', 'contribution_collection_id', array(
            'label'        => 'Contribution Collection',
            'description'  => 'The collection to which contributions will be added. Changes here will only affect new contributions.',
            'multiOptions' => $this->_getCollectionSelectOptions()
        ));
        $this->addElement('select', 'contribution_default_type', array(
            'label'        => 'Default Contribution Type',
            'description'  => 'The type that will be chosen for contributors by default.',
            'multiOptions' => $this->_getTypeSelectOptions()
        ));
        $this->addElement('submit', 'contribution_settings_submit', array(
            'label' => 'Save Settings'
        ));
        
        $this->addDisplayGroup(
            array('contribution_page_path', 'contribution_contributor_email',
                'contribution_consent_text', 'contribution_collection_id',
                'contribution_default_type'),
            'contribution_settings'
        );
                
        $this->addDisplayGroup(array('contribution_settings_submit'), 'submit');
    }
    
    private function _getCollectionSelectOptions()
    {
        $collections = get_db()->getTable('Collection')->findPairsForSelectForm();
        
        return array('' => 'Do not put contributions in any collection') + $collections;
    }

    private function _getTypeSelectOptions()
    {
        $types = get_db()->getTable('ContributionType')->findPairsForSelectForm();

        return array('' => 'No default type') + $types;
    }
    
    /**
     * Overrides the default decorators in Omeka Form to remove escaping from element descriptions.
     **/
    public function getDefaultElementDecorators()
    {       
        return array(
            'ViewHelper', 
            'Errors', 
            array(array('InputsTag' => 'HtmlTag'), array('tag' => 'div', 'class' => 'inputs')), 
            array('Description', array('tag' => 'p', 'class' => 'hint', 'escape' => false)), 
            'Label', 
            array(array('FieldTag' => 'HtmlTag'), array('tag' => 'div', 'class' => 'field'))
        );
    }
}
