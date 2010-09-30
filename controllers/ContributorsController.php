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
class Contribution_ContributorsController extends Omeka_Controller_Action
{
    public function init()
    {
        $modelName = 'ContributionContributor';
        if (version_compare(OMEKA_VERSION, '2.0-dev', '>=')) {
            $this->_helper->db->setDefaultModelName($modelName);
        } else {
            $this->_modelClass = $modelName;
        }
        $this->_browseRecordsPerPage = get_option('per_page_admin');
    }
    
    /**
     * Index action; simply forwards to browse.
     */
    public function indexAction()
    {
        $this->_forward('browse');
    }
}
