<?php 
/**
 * @version $Id$
 * @author CHNM
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
    
    public function addAction()
    {
        $table = $this->getTable();
    }
    
    public function editAction()
    {
        $contributionType = $this->findById();

        if(!empty($_POST)) {
            $contributionType->saveForm($_POST);
            $this->redirect->gotoSimple('');
        } else {
            $contributionTypeElements = $contributionType->ContributionTypeElements;
            $itemType = $contributionType->ItemType;
            $elements = $itemType->Elements;

            $this->view->contributionType = $contributionType;
            $this->view->itemType = $itemType;
            $this->view->elements = $elements;
            $this->view->contributionTypeElements = $contributionTypeElements;
        }
    }
}