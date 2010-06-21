<?php
/**
 * @version $Id$
 * @author CHNM
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */

define('CONTRIBUTION_PLUGIN_DIR', dirname(__FILE__));
define('CONTRIBUTION_HELPERS_DIR', CONTRIBUTION_PLUGIN_DIR
                                 . DIRECTORY_SEPARATOR
                                 . 'helpers');

require_once CONTRIBUTION_HELPERS_DIR . DIRECTORY_SEPARATOR
                                      . 'ThemeHelpers.php';

/**
* Contribution plugin class
*/
class Contribution
{
    private $_db;
    
    /**
     * Initializes instance properties and hooks the plugin into Omeka.
     */
    public function __construct()
    {
        $this->_db = get_db();
        self::addHooksAndFilters();
    }
    
    /**
     * Centralized location where plugin hooks and filters are added
     */
    public function addHooksAndFilters()
    {
        add_plugin_hook('install', array($this, 'install'));
        add_plugin_hook('uninstall', array($this, 'uninstall'));
    }
    
    /**
     * Contribution install hook
     */
    public function install()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->_db->prefix}contribution_types` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `item_type_id` INT UNSIGNED NOT NULL,
            `alias` VARCHAR(255) NOT NULL,
            `file_allowed` TINYINT(1) NOT NULL,
            `file_required` TINYINT(1) NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `item_type_id` (`item_type_id`)
            ) ENGINE=MyISAM;";
        $this->_db->query($sql);
        
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->_db->prefix}contribution_type_elements` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `type_id` INT UNSIGNED NOT NULL,
            `alias` VARCHAR(255) NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `type_id` (`type_id`)
            ) ENGINE=MyISAM;";
        $this->_db->query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `{$this->_db->prefix}contribution_contributors` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(255) NOT NULL,
            `email` VARCHAR(255) NOT NULL,
            `ip_address` INT UNSIGNED NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `email` (`email`)
            ) ENGINE=MyISAM;";
        $this->_db->query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `{$this->_db->prefix}contribution_contributed_items` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `item_id` INT UNSIGNED NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `item_id` (`item_id`)
            ) ENGINE=MyISAM;";
        $this->_db->query($sql);

        $this->_createContributorElementSet();
    }

    public function uninstall()
    {
        $sql = "DROP TABLE IF EXISTS
            {$this->_db->prefix}contribution_types,
            {$this->_db->prefix}contriubtion_type_elements,
            {$this->_db->prefix}contribution_contributors,
            {$this->_db->prefix}contribution_contributed_items;";
        $this->_db->query($sql);

        $recordTypeTable = $this->_db->getTable('RecordType');
        $recordTypeId = $recordTypeTable->findIdFromName('ContributionContributor');
        if ($recordTypeId !== null) {
            $recordType = $recordTypeTable->find($recordTypeId);
            $recordType->delete();
        }

        $elementSetTable = $this->_db->getTable('ElementSet');
        $elementSetId = $elementSetTable->findIdFromName('Contributor Information');
        if ($elementSetId !== null) {
            $elementSet = $elementSetTable->find($elementSetId);
            $elementSet->delete();
        }
        
        // For now, leave the element set there
        // Probably will make sense to delete it, since the contributors it
        // goes with will be gone.
    }
    
    private function _createContributorElementSet()
    {
        $recordType = new RecordType;
        $recordType->name = 'ContributionContributor';
        $recordType->description = 'Installed by the Contribution plugin. Elements assigned to this record type apply only to Contributors.';
        $recordType->save();

        $elementSet = new ElementSet;
        $elementSet->name = 'Contributor Information';
        $elementSet->description = 'Installed by the Contribution plugin. Stores metadata about contributors.';
        $elementSet->record_type_id = $recordType->id;
        $elementSet->save();

        $element = new Element;
        $element->name = 'Contributor Name';
        $element->description = 'Name';
        $element->element_set_id = $elementSet->id;
        $element->record_type_id = $recordType->id;
        $element->save();
    }
}
new Contribution;
