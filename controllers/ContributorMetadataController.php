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
class Contribution_ContributorMetadataController extends Omeka_Controller_Action
{
    public function init()
    {
        $modelName = 'ContributionContributorField';
        if (version_compare(OMEKA_VERSION, '2.0-dev', '>=')) {
            $this->_helper->db->setDefaultModelName($modelName);
        } else {
            $this->_modelClass = $modelName;
        }
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
        $this->redirect->goto('');
    }

    protected function  _getAddSuccessMessage($record)
    {
        return 'Question successfully added.';
    }

    protected function _getEditSuccessMessage($record)
    {
        return 'Question successfully updated.';
    }

    protected function _getDeleteSuccessMessage($record)
    {
        return 'Question deleted.';
    }
}