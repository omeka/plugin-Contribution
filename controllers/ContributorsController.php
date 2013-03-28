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
{
    public function init()
    {
      //  $this->_helper->db->setDefaultModelName('User');
    }

    
    public function showAction()
    {
        $id = $this->getParam('id');
        $user = $this->_helper->db->getTable('User')->find($id);
        $this->view->contributor = $user;
        $items = $this->_helper->db->getTable('ContributionContributedItem')->findBy(array('contributor'=>$user->id));
        $this->view->items = $items;
    }
}
