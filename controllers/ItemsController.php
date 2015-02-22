<?php

class Contribution_ItemsController extends Omeka_Controller_AbstractActionController
{
    public function init()
    {
        $this->_helper->db->setDefaultModelName('ContributionContributedItem');
    }
}