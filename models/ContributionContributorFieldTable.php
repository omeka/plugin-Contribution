<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 * @subpackage Models
 */

/**
 * Table of contributor-specific questions.
 *
 * @package Contribution
 * @subpackage Models
 */
class ContributionContributorFieldTable extends Omeka_Db_Table
{
    public function getSelect()
    {
        $select = parent::getSelect();
        $select->order('order ASC');
        return $select;
    }
}
