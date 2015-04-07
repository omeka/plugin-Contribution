<?php 
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */
 
/**
 * Controller for editing and viewing Contribution plugin contributors.
 */
class Contribution_ContributorsController extends Omeka_Controller_AbstractActionController
     implements Zend_Acl_Resource_Interface
{
    
    public function init()
    {
        $this->_helper->db->setDefaultModelName('ContributionContributor');
        $this->_browseRecordsPerPage = get_option('per_page_admin');
    }
    
    /**
     * Index action; simply forwards to browse.
     */
    public function indexAction()
    {
        $this->_forward('browse');
    }
    
    public function getResourceId()
    {
        return 'Contribution_Contributors';
    } 
}
