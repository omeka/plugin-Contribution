<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 * @subpackage Models
 */

/**
 * Record for contributor-specific questions.
 *
 * @package Contribution
 * @subpackage Models
 */
class ContributionContributorField extends Omeka_Record
{
    public $name;
    public $prompt;
    public $type;
    public $order;
}
