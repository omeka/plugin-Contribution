<?php

class Contribution_ItemsController extends Omeka_Controller_AbstractActionController
{
    
    public function _getBrowseRecordsPerPage()
    {
        if (is_admin_theme()) {
            return (int) get_option('per_page_admin');
        } else {
            return (int) get_option('per_page_public');
        }
    }    
    
    public function init()
    {
        $this->_helper->db->setDefaultModelName('ContributionContributedItem');
    }  
    
}