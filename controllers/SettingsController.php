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
        $form = $this->_getForm();
        $defaults = $this->_getOptions();
        $form->setDefaults($defaults);

        if (isset($_POST['submit'])) {
            if ($form->isValid($_POST)) {
                $this->_setOptions($form->getValues());
                $this->_helper->flashMessenger(__('Settings have been saved.'));
            } else {
                $this->_helper->flashMessenger(__('There were errors found in your form. Please edit and resubmit.', 'error'));
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
        $options = array();
        $cnt = new ContributionPlugin();
        $pluginOptions = $cnt->getOptions();
        foreach ($pluginOptions as $option) {
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
        $cnt = new ContributionPlugin();
        $options = $cnt->getOptions();
        foreach ($newOptions as $optionName => $optionValue) {
            if (in_array($optionName, $options)) {
                set_option($optionName, $optionValue);
            }
        }
    }

    private function _getForm()
    {
        $form = new Omeka_Form_Admin(array('type'=>'contribution_settings'));

        $form->addElementToEditGroup('text', 'contribution_page_path', array(
            'label'       => __('Contribution Slug'),
            'description' => __('Relative path from the Omeka root to the desired location for the contribution form. If left blank, the default path will be named &#8220;contribution.&#8221;'),
            'filters'     => array(array('StringTrim', '/\\\s'))
        ));

        $form->addElementToEditGroup('text', 'contribution_email_sender', array(
            'label'       => __('Contribution Confirmation Email'),
            'description' => __('An email message will be sent to each contributor from this address confirming that they submitted a contribution to this website. Leave blank if you do not want an email sent.'),
            'validators'  => array('EmailAddress')
        ));

        $form->addElementToEditGroup('textarea', 'contribution_email_recipients', array(
            'label'       => __('New Contribution Notification Emails'),
            'description' => __('An email message will be sent to each address here whenever a new item is contributed. Leave blank if you do not want anyone to be alerted of contributions by email.'),
            'attribs'     => array('rows' => '5')
        ));

        $form->addElementToEditGroup('textarea', 'contribution_consent_text', array(
            'label'       => __('Text of Terms of Service'),
            'description' => __('The text of the legal disclaimer to which contributors will agree.'),
            'attribs'     => array('class' => 'html-editor', 'rows' => '15')
        ));

        $form->addElementToEditGroup('checkbox', 'contribution_simple', array(
            'label' => __("Use 'Simple' Options"),
            'description' => __("This will require an email address from contributors, and create a guest user from that information. If those users want to use the account, they will have to request a new password for the account. If you want to collect additional information about contributors, you cannot use the simple option. See <a href='http://omeka.org/codex/Plugins/Contribution_2.0'>documentation</a> for details. "),
            ),
            array('checked'=> (bool) get_option('contribution_simple') ? 'checked' : '')
        );

        $form->addElementToEditGroup('textarea', 'contribution_email', array(
            'label' => __("Email text to send to contributors"),
            'description' => __("Email text to send to contributors when they submit an item. A link to their contribution will be appended. If using the 'Simple' option, we recommend that you notify contributors that a guest user account has been created for them, and what they gain by confirming their account."),
            'attribs'     => array('class' => 'html-editor', 'rows' => '15')
        ));

        $collections = get_db()->getTable('Collection')->findPairsForSelectForm();
        $collections = array('' => __('Do not put contributions in any collection')) + $collections;

        $form->addElementToEditGroup('select', 'contribution_collection_id', array(
            'label'        => __('Contribution Collection'),
            'description'  => __('The collection to which contributions will be added. Changes here will only affect new contributions.'),
            'multiOptions' => $collections
        ));

        $types = get_db()->getTable('ContributionType')->findPairsForSelectForm();
        $types = array('' => __('No default type')) + $types;

        $form->addElementToEditGroup('select', 'contribution_default_type', array(
            'label'        => __('Default Contribution Type'),
            'description'  => __('The type that will be chosen for contributors by default.'),
            'multiOptions' => $types
        ));

        if(plugin_is_active('UserProfiles')) {
            $profileTypes = $this->_helper->db->getTable('UserProfilesType')->findPairsForSelectForm();
            $form->addElementToEditGroup('select', 'contribution_user_profile_type', array(
                'label' => __('Choose a profile type for contributors'),
                'description' => __('Configure the profile type under User Profiles'),
                'multiOptions' => array('' => __("None")) + $profileTypes
            ));
        }

       return $form;
    }
}
