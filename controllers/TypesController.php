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
	/**
	 * Index action; simply forwards to contributeAction.
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
	    $table = get_db()->getTable('ContributionType');
	    $typeInfoArray = $table->getBrowseData();
	    
	    $this->view->typeInfoArray = $typeInfoArray;
	}
}