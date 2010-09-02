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
class Contribution_ContributorsController extends Omeka_Controller_Action
{
    public function init()
    {
        $this->_modelClass = 'ContributionContributor';
        $this->_browseRecordsPerPage = get_option('per_page_admin');
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
    /*
    public function browseAction()
    {
        $table = $this->getTable();
        $browseData = $table->getBrowseData();

        $this->view->browseData = $browseData;
    }*/
}