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
class Contribution_TypesController extends Omeka_Controller_Action
{
    public function init()
    {
        $this->_modelClass = 'ContributionType';
    }
    
    /**
     * Index action; simply forwards to browse.
     */
    public function indexAction()
    {
        $this->_forward('browse');
    }
    
    /**
     * Browse action
     */
    public function browseAction()
    {
        $table = $this->getTable();
        $typeInfoArray = $table->getBrowseData();
        
        $this->view->typeInfoArray = $typeInfoArray;
    }
    
    public function multipleAddAction()
    {
        if (!empty($_POST)) {
            $newTypes = $_POST['newTypes'];
            if (is_array($newTypes)) {
                foreach ($newTypes as $newType) {
                    $contributionType = new ContributionType;
                    try {
                        $contributionType->saveForm($newType);
                        $this->flashSuccess('Types successfully added.');
                    } catch (Omeka_Validator_Exception $e) {
                        $this->flashValidationErrors($e, Omeka_Controller_Flash::DISPLAY_NEXT);
                    } catch (Exception $e) {
                        $this->flashError($e->getMessage());
                    }
                }
            }
        } else {
            $this->flashError('Types may only be added via POST.');
        }
        $this->redirect->goto('');
    }

    public function showAction()
    {
        $this->redirect->goto('');
    }

    protected function _getEditSuccessMessage($record)
    {
        return 'Type successfully updated.';
    }

    protected function _getDeleteSuccessMessage($record)
    {
        return 'Type deleted.';
    }
}