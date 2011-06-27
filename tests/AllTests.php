<?php
/**
 * @version $Id$
 * @copyright Center for History and New Media, 2007-2010
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package Omeka
 */

define('CONTRIBUTION_PLUGIN_DIR', dirname(dirname(__FILE__)));

/**
 * Test suite for Contribution plugin.
 *
 * @package Omeka
 * @copyright Center for History and New Media, 2007-2010
 */
class Contribution_AllTests extends PHPUnit_Framework_TestSuite
{
    public static function suite()
    {
        $suite = new Contribution_AllTests('Contribution Tests');
        $testCollector = new PHPUnit_Runner_IncludePathTestCollector(
          array(dirname(__FILE__) . '/cases')
        );
        $suite->addTestFiles($testCollector->collectTests());
        return $suite;
    }
}
