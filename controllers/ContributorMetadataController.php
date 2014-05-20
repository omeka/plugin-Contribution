<?php 
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */
 
/**
 * Controller for editing and viewing Contribution plugin contributor fields.
 */
class Contribution_ContributorMetadataController extends Omeka_Controller_AbstractActionController
{
    public function init()
    {
        $modelName = 'ContributionContributorField';
        $this->_helper->db->setDefaultModelName($modelName);
    }
    
    /**
     * Index action; simply forwards to browse.
     */
    public function indexAction()
    {
        $this->_forward('browse');
    }

    public function showAction()
    {
        $this->_helper->redirector->goto('browse');
    }

    protected function  _getAddSuccessMessage($record)
    {
        return __('Question successfully added.');
    }

    protected function _getEditSuccessMessage($record)
    {
        return __('Question successfully updated.');
    }

    protected function _getDeleteSuccessMessage($record)
    {
        return __('Question deleted.');
    }
}