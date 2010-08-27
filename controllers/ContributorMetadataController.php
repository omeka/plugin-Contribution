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
class Contribution_ContributorMetadataController extends Omeka_Controller_Action
{
    public function init()
    {
        $this->_modelClass = 'ContributionContributorField';
    }
    
    /**
     * Index action; simply forwards to browse.
     */
    public function indexAction()
    {
        $this->_forward('browse');
    }

    public function multipleAddAction()
    {
        if (!empty($_POST)) {
            $newFields = $_POST['newFields'];
            if (is_array($newFields)) {
                foreach ($newFields as $newField) {
                    $field = new ContributionContributorField;
                    $field->order = 0;
                    try {
                        $field->saveForm($newField);
                    } catch (Omeka_Validator_Exception $e) {
                        $this->flashValidationErrors($e);
                    } catch (Exception $e) {
                        $this->flashError($e->getMessage());
                    }
                }
            }
        } else {
            $this->flashError('Fields may only be added via POST.');
        }
        $this->redirect->goto('');
    }
}