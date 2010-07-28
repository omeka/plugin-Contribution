<?php
/**
 * @version $Id$
 * @copyright Center for History and New Media, 2010
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package Contribution
 * @subpackage Mixins
 */

/**
 * Modified version of Orderable that omits the post-save behavior.
 *
 * @package Contribution
 * @subpackage Mixins
 * @copyright Center for History and New Media, 2010
 */
class ContributionOrderable extends Orderable
{
    public function afterSaveForm($post) {}
}