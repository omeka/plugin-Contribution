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
    private static $hooks = array('install', 
                                  'uninstall',
                                  'admin_append_to_plugin_uninstall_message',
                                  'define_acl',
                                  'define_routes');
                                  
    private static $filters = array('admin_navigation_main');
    
    public static $options = array('contribution_page_path',
                                   'contribution_contributor_email',
                                   'contribution_consent_text',
                                   'contribution_collection_id',
                                   'contribution_recaptcha_public_key',
                                   'contribution_recaptcha_private_key');

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
        foreach (self::$hooks as $hookName) {
            $functionName = Inflector::variablize($hookName);
            add_plugin_hook($hookName, array($this, $functionName));
        }
        
        foreach (self::$filters as $filterName) {
            $functionName = Inflector::variablize($filterName);
            add_filter($filterName, array($this, $functionName));
        }
    }
    
    /**
     * Contribution install hook
     */
    public function install()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->_db->prefix}contribution_types` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `item_type_id` INT UNSIGNED NOT NULL,
            `display_name` VARCHAR(255) NOT NULL,
            `file_permissions` ENUM('Disallowed', 'Allowed', 'Required') DEFAULT 'Disallowed',
            PRIMARY KEY (`id`),
            UNIQUE KEY `item_type_id` (`item_type_id`)
            ) ENGINE=MyISAM;";
        $this->_db->query($sql);
        
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->_db->prefix}contribution_type_elements` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `type_id` INT UNSIGNED NOT NULL,
            `element_id` INT UNSIGNED NOT NULL,
            `prompt` VARCHAR(255) NOT NULL,
            `order` INT UNSIGNED NOT NULL,
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
        
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->_db->prefix}contribution_contributor_fields` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(255) NOT NULL,
            `description` VARCHAR(255),
            `type` ENUM('Text', 'Tiny Text') NOT NULL,
            `order` INT UNSIGNED NOT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=MyISAM;";
        $this->_db->query($sql);
        
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->_db->prefix}contribution_contributor_values` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `field_id` INT UNSIGNED NOT NULL,
            `contributor_id` INT UNSIGNED NOT NULL,
            `value` TEXT NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `field_id_contributor_id` (`field_id`, `contributor_id`)
            ) ENGINE=MyISAM;";
        $this->_db->query($sql);

        $this->_createDefaultContributionTypes();
    }

    /**
     * Contribution uninstall hook
     */
    public function uninstall()
    {
        // Delete all the Contribution options
        foreach (self::$options as $option) {
            delete_option($option);
        }
        
        // Drop all the Contribution tables
        $sql = "DROP TABLE IF EXISTS
            `{$this->_db->prefix}contribution_types`,
            `{$this->_db->prefix}contribution_type_elements`,
            `{$this->_db->prefix}contribution_contributors`,
            `{$this->_db->prefix}contribution_contributed_items`,
            `{$this->_db->prefix}contribution_contributor_fields`,
            `{$this->_db->prefix}contribution_contributor_values`;";
        $this->_db->query($sql);
    }
    
    public function adminAppendToPluginUninstallMessage()
    {
        echo '<p><strong>Warning</strong>: Uninstalling the Contribution plugin 
            will remove all information about contributors, as well as the
            data that marks which items in the archive were contributed.</p>
            <p>The contributed items themselves will remain.</p>';
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
        
        $resource = new Omeka_Acl_Resource('Contribution_Types');
        $resource->add(array('browse', 'add', 'edit'));
        $acl->add($resource);
        
        $acl->deny(null, 'Contribution_Types');
        $acl->allow('super', 'Contribution_Types');
        $acl->allow('admin', 'Contribution_Types');
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
    
    public function adminNavigationMain($nav)
    {
        $nav['Contribution'] = uri('contribution');
        return $nav;
    }
    
    private function _createDefaultContributionTypes()
    {
        $storyType = new ContributionType;
        $storyType->item_type_id = 1;
        $storyType->display_name = 'Story';
        $storyType->file_permissions = 'Allowed';
        $storyType->save();
        
        $textElement = new ContributionTypeElement;
        $textElement->type_id = $storyType->id;
        $textElement->element_id = 50;
        $textElement->prompt = 'Title';
        $textElement->order = 1;
        $textElement->save();
        
        $textElement = new ContributionTypeElement;
        $textElement->type_id = $storyType->id;
        $textElement->element_id = 1;
        $textElement->prompt = 'Story Text';
        $textElement->order = 2;
        $textElement->save();
        
        $imageType = new ContributionType;
        $imageType->item_type_id = 6;
        $imageType->display_name = 'Image';
        $imageType->file_permissions = 'Required';
        $imageType->save();
        
        $descriptionElement = new ContributionTypeElement;
        $descriptionElement->type_id = $imageType->id;
        $descriptionElement->element_id = 41;
        $descriptionElement->prompt = 'Image Description';
        $descriptionElement->order = 1;
        $descriptionElement->save();
    }
}
new Contribution;
