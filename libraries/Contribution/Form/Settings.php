<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */

/**
 * Contribution config form.
 */
class Contribution_Form_Settings extends Omeka_Form
{
    public function init()
    {
        $this->setOptions(array('type' => 'contribution_settings'));
        $this->setAttrib('id', 'settings-form');
        parent::init();

        $db = get_db();

        $this->addElement('text', 'contribution_page_path', array(
            'label' => __('Contribution Slug'),
            'description' => __('Relative path from the Omeka root to the desired location for the contribution form. Default path is &#8220;contribution.&#8221;'),
            'required' => true,
            'filters' => array(array('StringTrim', '/\\\s')),
        ));

        $this->addElement('text', 'contribution_email_sender', array(
            'label' => __('Contribution Confirmation Email'),
            'description' => __('An email message will be sent to each contributor from this address confirming that they submitted a contribution to this website. Leave blank if you do not want an email sent.'),
            'validators' => array('EmailAddress'),
        ));

        $this->addElement('textarea', 'contribution_email_recipients', array(
            'label' => __('New Contribution Notification Emails'),
            'description' => __('An email message will be sent to each address here whenever a new item is contributed. Leave blank if you do not want anyone to be alerted of contributions by email.'),
            'attribs' => array('rows' => '5'),
        ));

        $this->addElement('textarea', 'contribution_consent_text', array(
            'label' => __('Text of Terms of Service'),
            'description' => __('The text of the legal disclaimer to which contributors will agree.'),
            'attribs' => array('class' => 'html-editor', 'rows' => '15'),
        ));

        $this->addElement('checkbox', 'contribution_open', array(
            'label' => __("Allow Non-registered Contributions"),
            'description' => __("This will require an email address from contributors, and create a guest user from that information. If those users want to use the account, they will have to request a new password for the account. If you want to collect additional information about contributors, they must create an account. See <a href='http://omeka.org/codex/Plugins/Contribution_2.0'>documentation</a> for details. "),
            ),
            array('checked' => (bool) get_option('contribution_open') ? 'checked' : '')
        );

        $this->addElement('checkbox', 'contribution_strict_anonymous', array(
            'label' => __("Allow Anonymous Contributions"),
            'description' => __("If non-registered contributions are allowed above, this option allows contributors to remain completely anonymous, even to administrators. A dummy user account will be created that stores no identifing information. See <a href='http://omeka.org/codex/Plugins/Contribution_2.0'>documentation</a> for details. "),
            ),
            array('checked'=> (bool) get_option('contribution_strict_anonymous') ? 'checked' : '')
        );

        $this->addElement('textarea', 'contribution_email', array(
            'label' => __("Email text to send to contributors"),
            'description' => __("Email text to send to contributors when they submit an item. A link to their contribution will be appended. If using the 'Non-registered', but not 'Anonymous', options, we recommend that you notify contributors that a guest user account has been created for them, and what they gain by confirming their account."),
            'attribs' => array('class' => 'html-editor', 'rows' => '15'),
        ));

        $collections = $db->getTable('Collection')->findPairsForSelectForm();
        $collections = array('' => __('Do not put contributions in any collection')) + $collections;

        $this->addElement('select', 'contribution_collection_id', array(
            'label' => __('Contribution Collection'),
            'description' => __('The collection to which contributions will be added. Changes here will only affect new contributions.'),
            'multiOptions' => $collections,
        ));

        $types = $db->getTable('ContributionType')->findPairsForSelectForm();
        $types = array('' => __('No default type')) + $types;

        $this->addElement('select', 'contribution_default_type', array(
            'label' => __('Default Contribution Type'),
            'description' => __('The type that will be chosen for contributors by default.'),
            'multiOptions' => $types,
        ));

        if (plugin_is_active('UserProfiles')) {
            $profileTypes = $db->getTable('UserProfilesType')->findPairsForSelectForm();
            $this->addElement('select', 'contribution_user_profile_type', array(
                'label' => __('Choose a profile type for contributors'),
                'description' => __('Configure the profile type under User Profiles'),
                'multiOptions' => array('' => __("None")) + $profileTypes,
            ));
        }
    }

    public function getCurrentOptions()
    {
        $currents = array();
        $elements = $this->getElements();
        foreach ($elements as $element) {
            $option = $element->getName();
            $currents[$option] = get_option($option);
        }
        return $currents;
    }
}
