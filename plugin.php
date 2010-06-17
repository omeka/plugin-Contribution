<?php
/**
* Contribution plugin.
* @author CHNM
* @version $Id$
* @copyright Center for History and New Media, 2007-2010
* @package Contribution
*/

/**
* Contribution plugin class
*/
class Contribution
{
    private $db;
    
    /**
     * Initializes instance properties and hooks the plugin into Omeka.
     */
    public function __construct()
    {
        $this->db = get_db();
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
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->db->prefix}contribution_types` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `item_type_id` INT UNSIGNED NOT NULL,
            `alias` VARCHAR(255) NOT NULL,
            `file_allowed` TINYINT(1) NOT NULL,
            `file_required` TINYINT(1) NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `item_type_id` (`item_type_id`)
            ) ENGINE=MyISAM;";
        $this->db->query($sql);
        
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->db->prefix}contribution_type_elements` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `type_id` INT UNSIGNED NOT NULL,
            `alias` VARCHAR(255) NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `type_id` (`type_id`)
            ) ENGINE=MyISAM;";
        $this->db->query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `{$this->db->prefix}contribution_contributors` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `email` VARCHAR(255) NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `email` (`email`)
            ) ENGINE=MyISAM;";
        $this->db->query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `{$this->db->prefix}contribution_contributed_items` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `item_id` INT UNSIGNED NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `item_id` (`item_id`)
            ) ENGINE=MyISAM;";
        $this->db->query($sql);

        $this->_createContributorElementSet();
    }

    public function uninstall()
    {
        $sql = "DROP TABLE IF EXISTS
            {$this->db->prefix}contribution_types,
            {$this->db->prefix}contriubtion_type_elements,
            {$this->db->prefix}contribution_contributors,
            {$this->db->prefix}contribution_contributed_items;";
        $this->db->query($sql);

        $recordTypeTable = $this->db->getTable('RecordType');
        $recordTypeId = $recordTypeTable->findIdFromName('ContributionContributor');
        if ($recordTypeId !== null) {
            $recordType = $recordTypeTable->find($recordTypeId);
            $recordType->delete();
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
