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
class Contribution_SettingsController extends Omeka_Controller_Action
{
    /**
     * Index action; simply forwards to contributeAction.
     */
    public function indexAction()
    {
        $this->_forward('edit');
    }
    
    /**
     * Edit action
     */
    public function editAction()
    {
        require CONTRIBUTION_FORMS_DIR . DIRECTORY_SEPARATOR . 'Settings.php';
        $form = new Contribution_Form_Settings;
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
        $options = array();
        foreach (ContributionPlugin::$options as $option) {
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
}
