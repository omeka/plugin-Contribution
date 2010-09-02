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
     * Retrieve a contributor by email address.
     * Emails are a unique identifier for contributors, so this only returns
     * one. 
     * 
     * @param string $email
     * @return ContributionContributor 
     */
    public function findByEmail($email)
    {
        $select = $this->getSelect();
        $select->where('`email` = ?', $email);
        return $this->fetchObject($select);
    }
} 