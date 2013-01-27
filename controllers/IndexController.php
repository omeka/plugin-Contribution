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
class Contribution_IndexController extends Omeka_Controller_AbstractActionController
{
    /**
     * Index action.
     */
    public function indexAction()
    {
        if(!is_allowed('Contribution_Settings', 'edit')) {
            $this->redirect('contribution/items');
        }
    }
}
