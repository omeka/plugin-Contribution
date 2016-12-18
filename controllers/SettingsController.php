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
        $form = new Contribution_Form_Settings;
        $defaults = $form->getCurrentOptions();
        $form->setDefaults($defaults);

        if (isset($_POST['submit'])) {
            if ($form->isValid($_POST)) {
                $options = array_keys($defaults);
                foreach ($form->getValues() as $optionName => $optionValue) {
                    if (in_array($optionName, $options)) {
                        set_option($optionName, $optionValue);
                    }
                }
                $this->_helper->flashMessenger(__('Settings have been saved.'), 'success');
            } else {
                $this->_helper->flashMessenger(__('There were errors found in your form. Please edit and resubmit.', 'error'));
            }
        }

        $this->view->form = $form;
    }
}
