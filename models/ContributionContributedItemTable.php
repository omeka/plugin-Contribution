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
class ContributionContributedItemTable extends Omeka_Db_Table
{
    public $item_id;
    public $contributor_id;
    public $public;

    public function findByItem($item)
    {
        if ($item instanceof Omeka_Record) {
            $itemId = $item->id;
        } else {
            $itemId = $item;
        }
        
        $select = $this->getSelect();
        $select->where('item_id = ?', $itemId, Zend_Db::PARAM_INT);
        return $this->fetchObject($select);
    }
}
