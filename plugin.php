<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */

define('CONTRIBUTION_PLUGIN_DIR', dirname(__FILE__));
define('CONTRIBUTION_HELPERS_DIR', CONTRIBUTION_PLUGIN_DIR
                                 . DIRECTORY_SEPARATOR
                                 . 'helpers');
define('CONTRIBUTION_FORMS_DIR', CONTRIBUTION_PLUGIN_DIR
                               . DIRECTORY_SEPARATOR
                               . 'forms');

require_once CONTRIBUTION_PLUGIN_DIR . DIRECTORY_SEPARATOR
           . 'ContributionPlugin.php';
require_once CONTRIBUTION_HELPERS_DIR . DIRECTORY_SEPARATOR
           . 'ThemeHelpers.php';


new ContributionPlugin;
