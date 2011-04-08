<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 * @subpackage Models
 */

/**
 * Record for individual contributors.
 *
 * @package Contribution
 * @subpackage Models
 */
class ContributionContributor extends Omeka_Record
{
    public $name;
    public $email;
    public $ip_address;
    
    protected $_related = array('Items' => 'getContributedItems');
    
    /**
     * Validate form submissions.
     * Gotta have a valid 1) ip address, 2) email address, 3) first & last name
     */
    protected function _validate()
    {       
        if(empty($this->ip_address)) {
            $this->addError('ip_address', 'Contributors must come from a valid IP address.');
        }
        
        if(!Zend_Validate::is($this->email, 'EmailAddress')) {
            $this->addError('email', 'The email address you have provided is invalid.  Please provide another one.');
        }
        
        if(empty($this->name)) {
            $this->addError('name', 'Please provide a complete name.');
        }
    }
    
    /**
     * Return the items that the contributor has contributed.
     *
     * @return array
     */
    public function getContributedItems()
    {
        $db = $this->getDb();
        $sql = <<<SQL
SELECT *
FROM `{$db->Item}` AS `i`
INNER JOIN `{$db->ContributionContributedItem}` AS `cci`
ON `i`.`id` = `cci`.`item_id`
WHERE `cci`.`contributor_id` = ?;
SQL;
        return $db->fetchObjects($sql, $this->id);
    }
    
    /**
     * Gets a standard-format IP address from the internal
     * integer representation.
     *
     * @return string
     */
    public function getDottedIpAddress()
    {
        if (!($ipAddress = $this->ip_address)) {
            return null;
        }
        if (($value = inet_ntop($ipAddress))) {
            return $value;
        } else {
            return long2ip($ipAddress);
        }
    }
    
    /**
     * Sets an IP dotted-quad address on the Contributor.
     * Converts to a integer in the process.
     *
     * @param string $dottedIpAddress
     */
    public function setDottedIpAddress($dottedIpAddress)
    {
        $this->ip_address = inet_pton($dottedIpAddress);
    }

    /**
     * Get the contributor information for this contributor.
     *
     * @return array
     */
    public function getMetadata()
    {
        $db = $this->getDb();
        $sql = <<<SQL
SELECT `ccf`.`prompt` AS `prompt`, `ccv`.`value` AS `value`
FROM `{$db->ContributionContributorField}` AS `ccf`
LEFT OUTER JOIN `{$db->ContributionContributorValue}` AS `ccv`
ON `ccf`.`id` = `ccv`.`field_id`
WHERE `ccv`.`contributor_id` = ?;
SQL;
        return $this->getDb()->fetchPairs($sql, $this->id);
    }
}
