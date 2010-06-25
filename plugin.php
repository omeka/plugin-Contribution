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
        add_plugin_hook('define_acl', array($this, 'defineAcl'));
        add_plugin_hook('define_routes', array($this, 'defineRoutes'));
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
            `element_id` INT UNSIGNED NOT NULL,
            `alias` VARCHAR(255) NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `type_id_element_id` (`type_id`, `element_id`)
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
        $this->_createDefaultContributionTypes();
    }

    /**
     * Contribution uninstall hook
     */
    public function uninstall()
    {
        $sql = "DROP TABLE IF EXISTS
            `{$this->_db->prefix}contribution_types`,
            `{$this->_db->prefix}contribution_type_elements`,
            `{$this->_db->prefix}contribution_contributors`,
            `{$this->_db->prefix}contribution_contributed_items`;";
        $this->_db->query($sql);

        $recordTypeTable = $this->_db->getTable('RecordType');
        $recordTypeId = $recordTypeTable->findIdFromName('ContributionContributor');
        if ($recordTypeId !== null) {
            $recordType = $recordTypeTable->find($recordTypeId);
            $recordType->delete();
        }

        $elementSetTable = $this->_db->getTable('ElementSet');
        $elementSet = $elementSetTable->findByName('Contributor Information');
        if ($elementSet !== null) {
            $elementSet->delete();
        }
    }
    
    /**
     * Contribution define_acl hook
     * Restricts access to admin-only controllers and actions.
     */
    public function defineAcl($acl)
    {
        $resource = new Omeka_Acl_Resource('Contribution_Settings');
        $resource->add(array('edit'));
        $acl->add($resource);
        
        $acl->deny(null, 'Contribution_Settings');
        $acl->allow('super', 'Contribution_Settings');
        $acl->allow('admin', 'Contribution_Settings');
    }
    
    /**
     * Contribution define_routes hook
     * Defines public-only routes that set the contribution controller as the
     * only accessible one.
     */
    public function defineRoutes($router)
    {
        // Only apply custom routes on public theme.
        // The wildcards on both routes make these routes always apply for the
        // contribution controller.
        if (!defined('ADMIN')) {        
            $router->addRoute('contributionPublic',
                new Zend_Controller_Router_Route('contribution/:action/*',
                    array('module'     => 'contribution',
                          'controller' => 'contribution',
                          'action'     => 'contribute')));
        
            // get the base path
        	$bp = get_option('contribution_page_path');

            if ($bp) {
                $router->addRoute('contributionCustom', 
                    new Zend_Controller_Router_Route("{$bp}/:action/*",
                        array('module' => 'contribution',
                              'controller' => 'contribution',
                              'action' => 'contribute')));
            }
        }
    }
    
    private function _createDefaultContributionTypes()
    {
        $storyType = new ContributionType;
        $storyType->item_type_id = 1;
        $storyType->alias = 'Story';
        $storyType->file_allowed = 1;
        $storyType->file_required = 0;
        $storyType->save();
        
        $textElement = new ContributionTypeElement;
        $textElement->type_id = $storyType->id;
        $textElement->element_id = 1;
        $textElement->alias = 'Story Text';
        $textElement->save();
        
        $imageType = new ContributionType;
        $imageType->item_type_id = 6;
        $imageType->alias = 'Image';
        $imageType->file_allowed = 1;
        $imageType->file_required = 1;
        $imageType->save();
        
        $descriptionElement = new ContributionTypeElement;
        $descriptionElement->type_id = $imageType->id;
        $descriptionElement->element_id = 41;
        $descriptionElement->alias = 'Image Description';
        $descriptionElement->save();
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
