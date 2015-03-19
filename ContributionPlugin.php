<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */

define('CONTRIBUTION_PLUGIN_DIR', dirname(__FILE__));
define('CONTRIBUTION_HELPERS_DIR', CONTRIBUTION_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'helpers');
define('CONTRIBUTION_FORMS_DIR', CONTRIBUTION_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'forms');

require_once CONTRIBUTION_HELPERS_DIR . DIRECTORY_SEPARATOR . 'ThemeHelpers.php';


/**
 * Contribution plugin class
 *
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */
class ContributionPlugin extends Omeka_Plugin_AbstractPlugin
{
    /**
     * @var array Hooks for the plugin.
     */
    protected $_hooks = array(
        'initialize',
        'install',
        'uninstall',
        'upgrade',
        'define_acl',
        'define_routes',
        'uninstall_message',
        'admin_items_search',
        'admin_items_show_sidebar',
        'admin_items_browse_detailed_each',
        'items_browse_sql',
        'before_save_item',
        'after_delete_item',
    );

    /**
     * @var array Filters for the plugin.
     */
    protected $_filters = array(
        'admin_navigation_main',
        'public_navigation_main',
        'simple_vocab_routes',
        'item_citation',
        'item_search_filters',
        'guest_user_links',
        'guest_user_widgets',
        'api_resources',
        'api_import_omeka_adapters'
    );

    /**
     * @var array Options and their default values.
     */
    protected $_options = array(
        'contribution_page_path',
        'contribution_email_sender',
        'contribution_email_recipients',
        'contribution_consent_text',
        'contribution_collection_id',
        'contribution_default_type',
        'contribution_user_profile_type',
        'contribution_simple',
        'contribution_email',
    );

    public function setUp()
    {
        parent::setUp();
        if (plugin_is_active('UserProfiles')) {
            $this->_hooks[] = 'user_profiles_user_page';
        }

        if (! is_admin_theme()) {
            //dig up all the elements being used, and add their ElementForm hook
            $elementsTable = $this->_db->getTable('Element');
            $select = $elementsTable->getSelect();

            $select->join(array('contribution_type_elements' => $this->_db->ContributionTypeElement),
                    'element_id = elements.id', array());
            $elements = $elementsTable->fetchObjects($select);
            foreach ($elements as $element) {
                add_filter(array('ElementForm', 'Item', $element->set_name, $element->name ), array($this, 'elementFormFilter'), 2);
                add_filter(array('ElementInput', 'Item', $element->set_name, $element->name ), array($this, 'elementInputFilter'), 2);
            }
        }
    }

    /**
     * Add the translations.
     */
    public function hookInitialize()
    {
        add_translation_source(dirname(__FILE__) . '/languages');
    }

    /**
     * Contribution install hook
     */
    public function hookInstall()
    {
        $db = $this->_db;
        $sql = "CREATE TABLE IF NOT EXISTS `$db->ContributionType` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `item_type_id` INT UNSIGNED NOT NULL,
            `display_name` VARCHAR(255) NOT NULL,
            `file_permissions` ENUM('Disallowed', 'Allowed', 'Required') NOT NULL DEFAULT 'Disallowed',
            PRIMARY KEY (`id`),
            UNIQUE KEY `item_type_id` (`item_type_id`)
            ) ENGINE=MyISAM;";
        $this->_db->query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `$db->ContributionTypeElement` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `type_id` INT UNSIGNED NOT NULL,
            `element_id` INT UNSIGNED NOT NULL,
            `prompt` VARCHAR(255) NOT NULL,
            `order` INT UNSIGNED NOT NULL,
            `long_text` BOOLEAN DEFAULT TRUE,
            PRIMARY KEY (`id`),
            UNIQUE KEY `type_id_element_id` (`type_id`, `element_id`),
            KEY `order` (`order`)
            ) ENGINE=MyISAM;";
        $this->_db->query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `$db->ContributionContributedItem` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `item_id` INT UNSIGNED NOT NULL,
            `public` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
            `anonymous` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`),
            UNIQUE KEY `item_id` (`item_id`)
            ) ENGINE=MyISAM;";
        $this->_db->query($sql);

        $this->_createDefaultContributionTypes();
        set_option('contribution_email_recipients', get_option('administrator_email'));
    }

    /**
     * Contribution uninstall hook
     */
    public function hookUninstall()
    {
        // Delete all the Contribution options
        foreach ($this->_options as $option) {
            delete_option($option);
        }
        $db = $this->_db;
        // Drop all the Contribution tables
        $sql = "DROP TABLE IF EXISTS
            `$db->ContributionType`,
            `$db->ContributionTypeElement`,
            `$db->ContributionContributor`,
            `$db->ContributionContributedItem`,
            `$db->ContributionContributorField`,
            `$db->ContributionContributorValue`;";
        $this->_db->query($sql);
    }

    public function hookUpgrade($args)
    {
        $oldVersion = $args['old_version'];
        $newVersion = $args['new_version'];
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
            $this->hookInstall();

        }
        
            if (version_compare($oldVersion, '3.0', '<')) {
            if(!is_writable(CONTRIBUTION_PLUGIN_DIR . "/upgrade_files")) {
                throw new Omeka_Plugin_Installer_Exception("'upgrade_files' directory must be writable by the web server");
            }
            require_once(CONTRIBUTION_PLUGIN_DIR . '/libraries/ContributionImportUsers.php');
            //change contributors to real guest users
            Zend_Registry::get('bootstrap')->getResource('jobs')->sendLongRunning('ContributionImportUsers');
            //if the optional UserProfiles plugin is installed, handle the upgrade via the configuration page
            $sql = "ALTER TABLE `{$this->_db->ContributionTypeElement}` ADD `long_text` BOOLEAN DEFAULT TRUE";
            try {
                $this->_db->query($sql);
            } catch(Exception $e) {
                _log($e);
            }
            $contributionTypeElements = $this->_db->getTable('ContributionTypeElement')->findAll();
            foreach($contributionTypeElements as $typeElement) {
                $typeElement->long_text = true;
                $typeElement->save();
            }

            $sql = "
                ALTER TABLE `{$this->_db->ContributionContributedItem}` CHANGE `contributor_posting` `anonymous` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0';
                ";
            $this->_db->query($sql);
            $sql = "
                ALTER TABLE `{$this->_db->ContributionContributedItem}` DROP `contributor_id` ;
            ";
            $this->_db->query($sql);
            //clean up contributed item records if the corresponding item has been deleted
            //earlier verison of the plugin did not use the delete hook
            $sql = "DELETE  FROM `{$this->_db->ContributionContributedItem}` WHERE NOT EXISTS (SELECT 1 FROM `{$this->_db->prefix}items`  WHERE `{$this->_db->prefix}contribution_contributed_items`.`item_id` = `{$this->_db->prefix}items`.`id`)";

            $this->_db->query($sql);
        }
        
        if (version_compare($oldVersion, '3.0.2', '<')) {
            //fix some previous bad upgrades
            //need to check if contributor_posting was properly changed to anonymous
            $sql = "SHOW COLUMNS IN `{$this->_db->ContributionContributedItem}`";
            $result = $this->_db->query($sql);
            $cols = $result->fetchAll(Zend_Db::FETCH_COLUMN);

            if(in_array('contributor_posting', $cols)) {
                $sql = "
                    ALTER TABLE `{$this->_db->ContributionContributedItem}` CHANGE `contributor_posting` `anonymous` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0';
                    ";
                $this->_db->query($sql);
            } else if (! in_array('anonymous', $cols)) {
                $sql = "
                    ALTER TABLE `{$this->_db->ContributionContributedItem}` ADD `anonymous` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0';
                    ";
                $this->_db->query($sql);
            }

            if(in_array('contributor_id', $cols)) {
                $sql = "
                    ALTER TABLE `{$this->_db->ContributionContributedItem}` DROP `contributor_id` ;
                ";
                $this->_db->query($sql);
            }
        }
    }

    public function hookUninstallMessage()
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
    public function hookDefineAcl($args)
    {
        $acl = $args['acl'];
        $acl->addResource('Contribution_Contribution');
        $acl->allow(array('super', 'admin', 'researcher', 'contributor'), 'Contribution_Contribution');
        if (get_option('contribution_simple')) {
            $acl->allow(null, 'Contribution_Contribution', array('show', 'contribute', 'thankyou', 'my-contributions', 'type-form'));
        } else {
            $acl->allow('guest', 'Contribution_Contribution', array('show', 'contribute', 'thankyou', 'my-contributions', 'type-form'));
        }

        $acl->allow(null, 'Contribution_Contribution', array('contribute', 'terms', 'thankyou'));

        $acl->addResource('Contribution_Contributors');
        $acl->allow(null, 'Contribution_Contributors');

        $acl->addResource('Contribution_Items');
        $acl->allow(null, 'Contribution_Items');
        $acl->allow('guest', 'Items', 'showSelfNotPublic');
        $acl->deny('guest', 'Contribution_Items');
        $acl->deny(array('researcher', 'contributor'), 'Contribution_Items', 'view-anonymous');
        $acl->addResource('Contribution_Types');
        $acl->allow(array('super', 'admin'), 'Contribution_Types');
        $acl->addResource('Contribution_Settings');
        $acl->allow(array('super', 'admin'), 'Contribution_Settings');
    }

    /**
     * Contribution define_routes hook
     * Defines public-only routes that set the contribution controller as the
     * only accessible one.
     */
    public function hookDefineRoutes($args)
    {
        $router = $args['router'];
        // Only apply custom routes on public theme.
        // The wildcards on both routes make these routes always apply for the
        // contribution controller.

        // get the base path
        $bp = get_option('contribution_page_path');
        if ($bp) {
            $router->addRoute('contributionCustom',
                new Zend_Controller_Router_Route("$bp/:action/*",
                    array('module'     => 'contribution',
                          'controller' => 'contribution',
                          'action'     => 'contribute')));
        } else {

            $router->addRoute('contributionDefault',
                  new Zend_Controller_Router_Route('contribution/:action/*',
                        array('module'     => 'contribution',
                              'controller' => 'contribution',
                              'action'     => 'contribute')));

        }

        if (is_admin_theme()) {
            $router->addRoute('contributionAdmin',
                new Zend_Controller_Router_Route('contribution/:controller/:action/*',
                    array('module' => 'contribution',
                          'controller' => 'index',
                          'action' => 'index')));
        }
    }

    public function filterApiResources($apiResources)
    {
        $apiResources['contributions'] = array(
                'record_type' => 'ContributionContributedItem',
                'actions' => array('get', 'index'),
                //'index_params' => array('record_type', 'record_id')
        );

        $apiResources['contribution_types'] = array(
                'record_type' => 'ContributionType',
                'actions'     => array('get', 'index')
        );

        $apiResources['contribution_type_elements'] = array(
                'record_type' => 'ContributionTypeElement',
                'actions'     => array('get', 'index')
        );
        return $apiResources;
    }
    
    public function filterApiImportOmekaAdapters($adapters, $args)
    {
        if (strpos($args['endpointUri'], 'omeka.net') !== false) {
            $contributedItemAdapter = 
                new ApiImport_ResponseAdapter_Omeka_GenericAdapter(null, $args['endpointUri'], 'ContributionContributedItem');
            $contributedItemAdapter->setResourceProperties(array('item' => 'Item'));
            $adapters['contributions'] = $contributedItemAdapter;
            
            $contributionTypeAdapter = 
                new ApiImport_ResponseAdapter_Omeka_GenericAdapter(null, $args['endpointUri'], 'ContributionType');
            $contributionTypeAdapter->setResourceProperties(array('item_type' => 'ItemType'));
            $adapters['contribution_types'] = $contributionTypeAdapter;
    
            $contributionTypeElementsAdapter =
                new ApiImport_ResponseAdapter_Omeka_GenericAdapter(null, $args['endpointUri'], 'ContributionTypeElement');
            $contributionTypeElementsAdapter->setResourceProperties(
                    array(
                         'element' => 'Element',
                         'type'    => 'ContributionType'
                         )
                    );
            $adapters['contribution_type_elements'] = $contributionTypeElementsAdapter;
        } else {
            $contributionContributorsAdapter = 
                new ApiImport_ResponseAdapter_OmekaNet_ContributorsAdapter(
                    null, $args['endpointUri'], 'User'
                    );
            $adapters['contribution_contributors'] = $contributionContributorsAdapter;

            $contributedItemAdapter = 
                new ApiImport_ResponseAdapter_OmekaNet_ContributedItemsAdapter(
                        null, $args['endpointUri'], 'ContributionContributedItem'
                    );
            $adapters['contribution_contributed_items'] = $contributedItemAdapter;
            $typesAdapter = 
                new ApiImport_ResponseAdapter_Omeka_GenericAdapter(null, $args['endpointUri'], 'ContributionType');
            $typesAdapter->setResourceProperties(array('item_type' => 'ItemType'));
            $adapters['contribution_types'] = $typesAdapter;
            $typeElementsAdapter = 
                new ApiImport_ResponseAdapter_Omeka_GenericAdapter(null, $args['endpointUri'], 'ContributionTypeElement');
            $typeElementsAdapter->setResourceProperties(
                    array('type' => 'ContributionType',
                          'element' => 'Element'
                    ));
            $adapters['contribution_type_elements'] = $typeElementsAdapter;
        }
        return $adapters;
    }
    /**
     * Append a Contribution entry to the admin navigation.
     *
     * @param array $nav
     * @return array
     */
    public function filterAdminNavigationMain($nav)
    {
        $contributionCount = get_db()->getTable('ContributionContributedItems')->count();
        if ($contributionCount > 0) {
            $uri = url('contribution/items?sort_field=added&sort_dir=d');
            $label = __('Contributed Items');
        } else {
            $uri = url('contribution/index');
            $label = __('Contribution');
        }

        $nav[] = array(
            'label' => $label,
            'uri' => $uri,
            'resource' => 'Contribution_Contribution',
            'privilege' => 'browse'
        );
        return $nav;
    }

    /**
     * Append a Contribution entry to the public navigation.
     *
     * @param array $nav
     * @return array
     */
    public function filterPublicNavigationMain($nav)
    {
        $nav[] = array(
            'label' => __('Contribute an Item'),
            'uri'   => contribution_contribute_url(),
            'visible' => true,
        );
        return $nav;
    }

    /**
     * Append routes that render element text form input.
     *
     * @param array $routes
     * @return array
     */
    public function filterSimpleVocabRoutes($routes)
    {

        $routes[] = array('module' => 'contribution',
                          'controller' => 'contribution',
                          'actions' => array('type-form', 'contribute'));
        return $routes;
    }

    public function filterItemSearchFilters($displayArray, $args)
    {
        $request_array = $args['request_array'];
        if (isset($request_array['status'])) {
            $displayArray['Status'] = $request_array['status'];
        }
        if (isset($request_array['contributor'])) {
            $displayArray['Contributor'] = $this->_db->getTable('User')->find($request_array['contributor'])->name;
        }
        return $displayArray;
    }

    /**
     * Append Contribution search selectors to the advanced search page.
     *
     * @return string HTML
     */
    public function hookAdminItemsSearch()
    {
        $html = '<div class="field">';
        $html .= '<div class="two columns alpha">';
        $html .= get_view()->formLabel('contributed', __('Contribution Status'));
        $html .= '</div>';
        $html .= '<div class="inputs five columns omega">';
        $html .= '<div class="input-block">';
        $html .= get_view()->formSelect('contributed', null, null, array(
           ''  => __('Select Below'),
           '1' => __('Only Contributed Items'),
           '0' => __('Only Non-Contributed Items')
        ));
        $html .= '</div></div></div>';
        echo $html;
    }

    public function hookAdminItemsShowSidebar($args)
    {

        $htmlBase = $this->_adminBaseInfo($args);
        echo "<div class='panel'>";
        echo "<h4>" . __("Contribution") . "</h4>";
        echo $htmlBase;
        echo "</div>";
    }

    public function hookAdminItemsBrowseDetailedEach($args)
    {
        echo $this->_adminBaseInfo($args);
    }

    /**
     * Deal with Contribution-specific search terms.
     *
     * @param Omeka_Db_Select $select
     * @param array $params
     */
    public function hookItemsBrowseSql($args)
    {

    $select = $args['select'];
    $params = $args['params'];

        if (($request = Zend_Controller_Front::getInstance()->getRequest())) {
            $db = get_db();

            $contributed = $request->get('contributed');
            if (isset($contributed)) {
                if ($contributed === '1') {
                    $select->joinInner(
                            array('cci' => $db->ContributionContributedItem),
                            'cci.item_id = items.id',
                            array()
                     );
                } else if ($contributed === '0') {
                    $select->where("items.id NOT IN (SELECT `item_id` FROM {$db->ContributionContributedItem})");
                }
            }

            $contributor_id = $request->get('contributor_id');
            if (is_numeric($contributor_id)) {
                $select->joinInner(
                        array('cci' => $db->ContributionContributedItem),
                       'cci.item_id = items.id',
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
        $elementTable = $this->_db->getTable('Element');
        $itemTypeTable = $this->_db->getTable('ItemType');
        $textItemType = $itemTypeTable->findByName('Text');
        if ($textItemType) {
            $storyType = new ContributionType;
            $storyType->item_type_id = $textItemType->id;
            $storyType->display_name = 'Story';
            $storyType->file_permissions = 'Allowed';
            $storyType->save();
            $textElement = new ContributionTypeElement;
            $textElement->type_id = $storyType->id;
            $dcTitleElement = $elementTable->findByElementSetNameAndElementName('Dublin Core', 'Title');
            $textElement->element_id = $dcTitleElement->id;
            $textElement->prompt = 'Title';
            $textElement->order = 1;
            $textElement->long_text = false;
            $textElement->save();
            $textElement = new ContributionTypeElement;
            $textElement->type_id = $storyType->id;
            $itemTypeMetadataTextElement = $elementTable->findByElementSetNameAndElementName('Item Type Metadata', 'Text');
            $textElement->element_id = $itemTypeMetadataTextElement->id;
            $textElement->prompt = 'Story Text';
            $textElement->order = 2;
            $textElement->long_text = true;
            $textElement->save();
        }
        $imageItemType = $itemTypeTable->findByName('Still Image');
        if ($imageItemType) {
            $imageType = new ContributionType;
            $imageType->item_type_id = 6;
            $imageType->display_name = 'Image';
            $imageType->file_permissions = 'Required';
            $imageType->save();

            $descriptionElement = new ContributionTypeElement;
            $descriptionElement->type_id = $imageType->id;
            $dcDescriptionElement = $elementTable->findByElementSetNameAndElementName('Dublin Core', 'Description');
            $descriptionElement->element_id = $dcDescriptionElement->id;
            $descriptionElement->prompt = 'Image Description';
            $descriptionElement->order = 1;
            $descriptionElement->long_text = true;
            $descriptionElement->save();
        }
    }

    public function hookBeforeSaveItem($args)
    {
      $item = $args['record'];
      if ($item->exists()) {
          //prevent admins from overriding the contributer's assertion of public vs private
          $contributionItem = $this->_db->getTable('ContributionContributedItem')->findByItem($item);
          if ($contributionItem) {
              if (!$contributionItem->public && $item->public) {
                  $item->public = false;
                  Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger')->addMessage("Cannot override contributor's desire to leave contribution private", 'error');
              }
          }
      }
    }

    public function hookAfterDeleteItem($args)
    {
        $item = $args['record'];
        $contributionItem = $this->_db->getTable('ContributionContributedItem')->findByItem($item);
        if ($contributionItem) {
            $contributionItem->delete();
        }
    }

    public function hookUserProfilesUserPage($args)
    {
        $user = $args['user'];
        $contributionCount = $this->_db->getTable('ContributionContributedItem')->count(array('contributor' => $user->id));
        if ($contributionCount !=0) {
            echo "<a href='" . url('contribution/contributors/show/id/' . $user->id) . "'>Contributed Items ($contributionCount)";
        }
    }

    public function filterItemCitation($cite,$args)
    {
        $item = $args['item'];
        if (!$item) {
            return $cite;
        }
        $contribItem = $this->_db->getTable('ContributionContributedItem')->findByItem($item);
        if (!$contribItem) {
            return $cite;
        }
        $title      = metadata('item',array('Dublin Core', 'Title'));
        $siteTitle  = strip_formatting(option('site_title'));
        $itemId     = $item->id;
        $accessDate = date('F j, Y');
        $uri        = html_escape(record_url($item, 'show', true));

        if ($contribItem->anonymous) {
            $cite = __("Anonymous, ");
        } else {
            $cite = $contribItem->Contributor->name . ", ";
        }

        $cite .= "&#8220;$title,&#8221; ";
        if ($siteTitle) {
            $cite .= "<em>$siteTitle</em>, ";
        }
        $cite .= "accessed $accessDate, ";
        $cite .= "$uri.";
        return $cite;
    }

    public function filterGuestUserLinks($nav)
    {
        $nav['Contribution'] = array(
            'label' => 'My Contributions',
             'uri' => contribution_contribute_url('my-contributions'),
        );
        return $nav;
    }

    public function filterGuestUserWidgets($widgets)
    {
        $user = current_user();
        $widget = array('label' => __('My Contributions'));
        $contributedItems = get_db()->getTable('ContributionContributedItem')->findBy(array('contributor' => $user->id), 5);
        if ($contributedItems) {
            $html = "<ul>";
            foreach ($contributedItems as $contributedItem) {
                $item = $contributedItem->Item;
                $html .= sprintf("<li>%s</li>", link_to($item, 'show', metadata($item, array('Dublin Core', 'Title'))));
            }
            $html .= "</ul>";
            $html .= sprintf('<a href="%s">%s</a>',
                contribution_contribute_url('my-contributions'),
                __('See all my contributions'));
        }
        else {
            $html = '<p>' . __('No contribution yet.') . '</p>';
        }
        $widget['content'] = $html;
        $widgets[] = $widget;
        return $widgets;
    }

    private function _adminBaseInfo($args)
    {
        $item = $args['item'];
        $contributedItem = $this->_db->getTable('ContributionContributedItem')->findByItem($item);
        if ($contributedItem) {
            $html = '';
            $name = $contributedItem->getContributor()->name;
            $html .= "<p><strong>" . __("Contributed by:") . "</strong><span class='contribution-contributor'> $name</span></p>";

            $publicMessage = '';
            if (is_allowed($item, 'edit')) {
                if ($contributedItem->public) {
                    $publicMessage = __("This item can be made public.");
                } else {
                    $publicMessage = __("This item cannot be made public.");
                }
                $html .= "<p><strong>$publicMessage</strong></p>";
            }
            return $html;
        }
    }

    private function _contributorsToGuestUsers($contributorsData)
    {
        $map = array();
            foreach ($contributorsData as $index => $contributor) {
            $user = new User();
            $user->email = $contributor['email'];
            $user->name = $contributor['name'];
            //make sure username is 6 chars long and unique
            //base it on the email to lessen character restriction problems
            $explodedEmail = explode('@', $user->email);
            $username = $explodedEmail[0];
            $username = str_replace('.', '', $username);
            $user->username = $username;
            $user->active = true;
            $user->role = 'guest';
            $user->setPassword($user->email);
            $user->save();
            $map[$contributor['id']] = $user->id;
            $activation = UsersActivations::factory($user);
            $activation->save();
            release_object($user);
            release_object($activation);
        }
        return $map;
    }

    public function _mapOwners($contribItemData, $map)
    {
        $itemTable = $this->_db->getTable('Item');
        foreach ($contribItemData as $contribItem) {
            $item = $itemTable->find($contribItem['item_id']);
            $item->owner_id = $map[$contribItem['contributor_id']];
            $item->save();
            release_object($item);
        }
    }

    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Remove the form controls
     *
     * @param array $components
     * @param array $args
     * @return NULL
     */
    public function elementInputFilter($components, $args)
    {
        $view = get_view();
        $element = $args['element'];
        $type = $view->type;
        $contributionElement = $this->_db->getTable('ContributionTypeElement')->findByElementAndType($element, $type);
        if ($contributionElement->long_text == 0) {
            $components['input'] = $view->formText($args['input_name_stem'] . '[text]', $args['value']);
        }
        $components['form_controls'] = null;
        $components['html_checkbox'] = null;
        return $components;
    }

    /**
     * Replace the prompt and remove the add input button
     * @param array $components
     * @param array $args
     */
    public function elementFormFilter($components, $args)
    {
        $element = $args['element'];
        $view = get_view();
        $type = $view->type;
        $contributionElement = $this->_db->getTable('ContributionTypeElement')->findByElementAndType($element, $type);
        $prompt = $contributionElement->prompt;
        $components['label'] = '<label>' . $prompt . '</label>';
        $components['add_input'] = null;
        return $components;
    }
}
