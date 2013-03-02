<?php

class Contribution_ItemsController extends Omeka_Controller_AbstractActionController
{
    
    public function init()
    {
        $this->_helper->db->setDefaultModelName('ContributionContributedItem');
        $this->_browseRecordsPerPage = get_option('per_page_admin');        
    }
    
    
    public function browseAction()
    {
        $db = get_db();
        $contributors = $db->getTable('ContributionContributedItem')->findAllContributors();
        if($contributors) {
            asort($contributors);
        }
        $this->view->contributors = $contributors;
        parent::browseAction();        
    }
}