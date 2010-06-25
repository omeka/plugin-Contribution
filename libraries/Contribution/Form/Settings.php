<?php
/**
 * @version $Id$
 * @author CHNM
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
        $this->setAttrib('id', 'contribution-settings-form');
        
        // Should have a StringTrim('/') here, but it's broken until 1.10
        $this->addElement('text', 'contribution_page_path', array(
            'label'       => 'Contribution Form Path',
            'description' => 'Relative path from the Omeka root to the '
                           . 'desired location for the contribution form.'
        ));
        $this->addElement('text', 'contribution_contributor_email', array(
            'label'       => 'New Contribution Email Sender',
            'description' => 'If specified, an email message will be sent to '
                           . 'each contributor from this address.  Leave blank '
                           . 'if you do not want an email to be sent to '
                           . 'contributors.',
            'validators'  => array('EmailAddress')
        ));
        $this->addElement('textarea', 'contribution_terms_text', array(
            'label'       => 'Text of Terms',
            'description' => 'The text of the legal disclaimer contributors '
                           . 'will agree to.'
        ));
        $this->addElement('select', 'contribution_collection_id', array(
            'label'        => 'Contribution Collection',
            'description'  => 'The collection that contributions will be '
                            . 'added to.',
            'multiOptions' => $this->_getCollectionSelectOptions()
        ));
        $this->addElement('text', 'contribution_recaptcha_public_key', array(
            'label'       => 'reCAPTCHA Public Key',
            'description' => 'To enable CAPTCHA for the contribution form, please obtain a <a href="http://recaptcha.net/">reCAPTCHA</a> API key and enter the relevant values.'
        ));
        $this->addElement('text', 'contribution_recaptcha_private_key', array(
            'label'       => 'reCAPTCHA Private Key'
        ));
        $this->addElement('submit', 'contribution_settings_submit', array(
            'label' => 'Save Settings'
        ));
        
        $this->addDisplayGroup(
            array('contribution_page_path', 'contribution_contributor_email',
                'contribution_terms_text', 'contribution_collection_id',
                'contribution_recaptcha_public_key',
                'contribution_recaptcha_private_key'), 'contribution_settings');
                
        $this->addDisplayGroup(array('contribution_settings_submit'), 'submit');
    }
    
    private function _getCollectionSelectOptions()
    {
        $collections = get_db()->getTable('Collection')->findPairsForSelectForm();
        
        return array('' => 'Do not put contributions in any collection') + $collections;
    }
}