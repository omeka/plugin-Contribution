<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 * @subpackage Models
 */

/**
 * Table for ContributionContributor objects.
 *
 * @package Contribution
 * @subpackage Models
 */
class ContributionContributorTable extends Omeka_Db_Table
{
    /**
     * Retrieve a contributor by email address/name combination.
     * 
     * @param string $email
     * @param string $name
     * @return ContributionContributor 
     */
    public function findUnique($email, $name)
    {
        $select = $this->getSelect();
        $select->where('`email` = ?', $email)
               ->where('`name` = ?', $name);
        return $this->fetchObject($select);
    }
} 