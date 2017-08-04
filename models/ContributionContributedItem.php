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
    public $deleted = 0;

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
        $owner = $this->Item->getOwner();
        //if the user has been deleted, make a fake user called "Deleted User"
        if(!$owner) {
            $owner = new User();
            $owner->name = '[' . __('Unknown User') . ']';
            return $owner;
        }
        $user = current_user();
        if($user && $user->id == $owner->id) {
            return $owner;
        }
        //mimic an actual user, but anonymous if user doesn't have access
        if($this->anonymous == 1 && !is_allowed('Contribution_Items', 'view-anonymous')) {
            $owner = new User();
            $owner->name = __('Anonymous');
        }
        return $owner;
    }

    /**
     * Before-save hook.
     *
     * @param array $args
     */
    protected function beforeSave($args)
    {
        // Delete a contributed item. In fact, for security reason, make it
        // private and invisible to contributor.
        if ($this->deleted) {
            $this->public = false;
        }
    }

    /**
     * After-save hook.
     *
     * @param array $args
     */
    protected function afterSave($args)
    {
        if (!$this->public) {
            $item = $this->Item;
            if ($item->public) {
                $item->public = false;
                $item->save(false);
            }
            release_object($item);
        }
    }
}
