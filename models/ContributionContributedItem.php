<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 * @subpackage Models
 */

/**
 * Record that keeps track of contributions; links items to contributors.
 */

class ContributionContributedItem extends Omeka_Record_AbstractRecord
{
    public $id;
    public $item_id;
    public $public;
    public $anonymous;
    
    protected $_related = array(
        'Item' => 'getItem',
        'Contributor' => 'getContributor'
        );
    
    public function getItem()
    {
        return $this->getDb()->getTable('Item')->find($this->item_id);
    }


    public function getContributor()
    {
        //mimic an actual user, but anonymous
        if($this->anonymous == 1 && !is_allowed('Contribution_Items', 'view-anonymous')) {
            $owner = new User();
            $owner->name = __('Anonymous');
        } else {
            $owner = $this->Item->getOwner();
        }
        return $owner;
    }
}
