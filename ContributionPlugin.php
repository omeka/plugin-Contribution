<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */

/**
 * Contribution plugin class
 *
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */
class ContributionPlugin
{
    private static $_hooks = array(
        'install',
        'uninstall',
        'upgrade',
        'define_acl',
        'define_routes',
        'admin_append_to_plugin_uninstall_message',
        'admin_append_to_advanced_search',
        'admin_append_to_items_show_secondary',
        'admin_append_to_items_browse_detailed_each',
        'item_browse_sql'
    );

    private static $_filters = array(
        'admin_navigation_main',
        'public_navigation_main', 
        'simple_vocab_routes');

    public static $options = array(
        'contribution_page_path',
        'contribution_email_sender',
        'contribution_email_recipients',
        'contribution_consent_text',
        'contribution_collection_id',
        'contribution_default_type'
    );

    private $_db;

    /**
     * Initializes instance properties and hooks the plugin into Omeka.
     */
    public function __construct()
    {
        $this->_db = get_db();
        $this->addHooksAndFilters();
    }

    /**
     * Centralized location where plugin hooks and filters are added
     */
    public function addHooksAndFilters()
    {
        foreach (self::$_hooks as $hookName) {
            $functionName = Inflector::variablize($hookName);
            add_plugin_hook($hookName, array($this, $functionName));
        }

        foreach (self::$_filters as $filterName) {
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
            `file_permissions` ENUM('Disallowed', 'Allowed', 'Required') NOT NULL DEFAULT 'Disallowed',
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
            UNIQUE KEY `type_id_element_id` (`type_id`, `element_id`),
            KEY `order` (`order`)
            ) ENGINE=MyISAM;";
        $this->_db->query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `{$this->_db->prefix}contribution_contributors` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(255) NOT NULL,
            `email` VARCHAR(255) NOT NULL,
            `ip_address` VARBINARY(128) NOT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=MyISAM;";
        $this->_db->query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `{$this->_db->prefix}contribution_contributed_items` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `item_id` INT UNSIGNED NOT NULL,
            `contributor_id` INT UNSIGNED NOT NULL,
            `public` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`),
            UNIQUE KEY `item_id` (`item_id`)
            ) ENGINE=MyISAM;";
        $this->_db->query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `{$this->_db->prefix}contribution_contributor_fields` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `prompt` VARCHAR(255) NOT NULL,
            `type` ENUM('Text', 'Tiny Text') NOT NULL,
            `order` INT UNSIGNED NOT NULL,
            PRIMARY KEY (`id`),
            KEY `order` (`order`)
            ) ENGINE=MyISAM;";
        $this->_db->query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `{$this->_db->prefix}contribution_contributor_values` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `field_id` INT UNSIGNED NOT NULL,
            `contributor_id` INT UNSIGNED NOT NULL,
            `value` TEXT NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `contributor_id_field_id` (`contributor_id`, `field_id`)
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

    public function upgrade($oldVersion, $newVersion)
    {
        // Catch-all for pre-2.0 versions
        if (version_compare($oldVersion, '2.0-dev', '<=')) {
            // Clean up old options
            delete_option('contribution_plugin_version');
            delete_option('contribution_db_migration');

            $emailSender = get_option('contribution_contributor_email');
            if (!empty($emailSender)) {
                set_option('contribution_email_sender', $emailSender);
            }

            $pagePath = get_option('contribution_page_path');
            if ($pagePath = 'contribution/') {
                delete_option('contribution_page_path');
            } else {
                set_option('contribution_page_path', trim($pagePath, '/'));
            }

            // Since this is an upgrade from an old version, we need to install
            // all our tables.
            $this->install();

            return;
        }
        // Switch statement for newer versions
        switch ($oldVersion) {
        case '2.0alpha':
            $sql = "ALTER TABLE `{$this->_db->prefix}contribution_contributor_fields` DROP `name`";
            $this->_db->query($sql);
        case '2.0beta':
            $sql = "ALTER TABLE `{$this->_db->prefix}contribution_contributors` MODIFY `ip_address` VARBINARY(128) NOT NULL";
            $this->_db->query($sql);
        }
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
        $resourceList = array(
            'Contribution_Contribution' => array('contribute', 'index', 'terms', 'thankyou', 'type-form'),
            'Contribution_Contributors' => array('browse', 'show'),
            'Contribution_ContributorMetadata' => array('browse', 'add', 'edit', 'delete'),
            'Contribution_Types' => array('browse', 'add', 'edit', 'delete'),
            'Contribution_Settings' => array('edit')
        );
        $acl->loadResourceList($resourceList);


        // By default, deny everyone access to all resources, then allow access
        // to only super and admin.
        foreach ($resourceList as $resource => $privileges) {
            $acl->deny(null, $resource);
            $acl->allow('super', $resource);
            $acl->allow('admin', $resource);
        }

        // Allow everybody access to the Contribution controller.
        $acl->allow(null, 'Contribution_Contribution');
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
            $router->addRoute('contributionDefault',
                new Zend_Controller_Router_Route('contribution/:action/*',
                    array('module'     => 'contribution',
                          'controller' => 'contribution',
                          'action'     => 'contribute')));

            // get the base path
            $bp = get_option('contribution_page_path');

            if ($bp) {
                $router->addRoute('contributionCustom',
                    new Zend_Controller_Router_Route("{$bp}/:action/*",
                        array('module'     => 'contribution',
                              'controller' => 'contribution',
                              'action'     => 'contribute')));
            }
        } else {
            $router->addRoute('contributionAdmin',
                new Zend_Controller_Router_Route('contribution/:controller/:action/:id',
                    array('module' => 'contribution')));
        }
    }

    /**
     * Append a Contribution entry to the admin navigation.
     *
     * @param array $nav
     * @return array
     */
    public function adminNavigationMain($nav)
    {
        if(has_permission('Contribution_Contributors', 'browse')) {
            $nav['Contribution'] = uri('contribution');
        }
        return $nav;
    }
    
    /**
     * Append a Contribution entry to the public navigation.
     *
     * @param array $nav
     * @return array
     */
    public function publicNavigationMain($nav)
    {
        $nav['Contribute an Item'] = contribution_contribute_url();
        return $nav;
    }
    
    /**
     * Append routes that render element text form input.
     * 
     * @param array $routes
     * @return array
     */
    public function simpleVocabRoutes($routes)
    {
        $routes[] = array('module' => 'contribution', 
                          'controller' => 'contribution', 
                          'actions' => array('type-form', 'contribute'));
        return $routes;
    }

    /**
     * Append Contribution search selectors to the advanced search page.
     *
     * @return string HTML
     */
    public function adminAppendToAdvancedSearch()
    {
        $html = '<div class="field">';
        $html .= __v()->formLabel('contributed', 'Contribution Status');
        $html .= '<div class="inputs">';
        $html .= __v()->formSelect('contributed', null, null, array(
           ''  => 'Select Below',
           '1' => 'Only Contributed Items',
           '0' => 'Only Non-Contributed Items'
        ));
        $html .= '</div></div>';
        echo $html;
    }

    public function adminAppendToItemsShowSecondary($item)
    {
        if ($contributor = contribution_get_item_contributor($item)) {
            if (!($name = contributor('Name', $contributor))) {
                $name = 'Anonymous';
            }
            $id = contributor('ID', $contributor);
            $uri = uri('contribution/contributors/show/id/') . $id;
            $publicMessage = contribution_is_item_public($item)
                           ? 'This item can be made public.'
                           : 'This item should not be made public.';
        ?>
<div class="info-panel">
    <h2>Contribution</h2>
    <p>This item was contributed by
        <a href="<?php echo $uri; ?>"><?php echo $name; ?></a>.
    </p>
    <p><strong><?php echo $publicMessage; ?></strong></p>
</div>
<?php
        }
    }

    public function adminAppendToItemsBrowseDetailedEach()
    {
        $item = get_current_item();
        if ($contributor = contribution_get_item_contributor($item)) {
            if (!($name = contributor('Name', $contributor))) {
                $name = 'Anonymous';
            }
            $id = contributor('ID', $contributor);
            $uri = uri('contribution/contributors/show/id/') . $id;
            $publicMessage = contribution_is_item_public($item)
                           ? 'This item can be made public.'
                           : 'This item should not be made public.';
        ?>
<h3>Contribution</h3>
<p>This item was contributed by
    <a href="<?php echo $uri; ?>"><?php echo $name; ?></a>.
</p>
<p><strong><?php echo $publicMessage; ?></strong></p>
<?php
        }
    }

    /**
     * Deal with Contribution-specific search terms.
     *
     * @param Omeka_Db_Select $select
     * @param array $params
     */
    public function itemBrowseSql($select, $params)
    {
        if (($request = Zend_Controller_Front::getInstance()->getRequest())) {
            $db = get_db();
            $contributed = $request->get('contributed');
            if (isset($contributed)) {
                if ($contributed === '1') {
                    $select->joinInner(
                            array('cci' => $db->ContributionContributedItem),
                            'cci.item_id = i.id',
                            array()
                     );
                } else if ($contributed === '0') {
                    $select->where("i.id NOT IN (SELECT `item_id` FROM {$db->ContributionContributedItem})");
                }
            }

            $contributor_id = $request->get('contributor_id');
            if (is_numeric($contributor_id)) {
                $select->joinInner(
                        array('cci' => $db->ContributionContributedItem),
                        'cci.item_id = i.id',
                        array('contributor_id')
                );
                $select->where('cci.contributor_id = ?', $contributor_id);
            }
        }
    }

    /**
     * Create reasonable default entries for contribution types.
     */
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
