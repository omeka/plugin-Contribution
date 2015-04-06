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

require_once CONTRIBUTION_HELPERS_DIR . DIRECTORY_SEPARATOR
. 'ThemeHelpers.php';


/**
 * Contribution plugin class
 *
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */
class ContributionPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $_hooks = array(
        'install',
        'uninstall',
        'upgrade',
        'define_acl',
        'define_routes',
        'uninstall_message',
        'admin_items_search',
        'public_items_search',
        'admin_items_show_sidebar',
        'admin_items_browse_detailed_each',
        'items_browse_sql',
        'before_save_item',
        'after_delete_item'
    );

    protected $_filters = array(
        'admin_navigation_main',
        'public_navigation_main',
        'simple_vocab_routes',
        'api_resources',
        'api_import_omeka_adapters'
        );

    protected $_options = array(
        'contribution_page_path',
        'contribution_email_sender',
        'contribution_email_recipients',
        'contribution_consent_text',
        'contribution_collection_id',
        'contribution_default_type'
    );

    public function getOptions()
    {
        return $this->_options;
    }

    public function setUp()
    {
        parent::setUp();
        if(! is_admin_theme()) {
            //dig up all the elements being used, and add their ElementForm hook
            $elementsTable = $this->_db->getTable('Element');
            $select = $elementsTable->getSelect();
            $select->join(array('contribution_type_elements' => $this->_db->ContributionTypeElement),
                    'element_id = elements.id', array());
            $elements = $elementsTable->fetchObjects($select);
            foreach($elements as $element) {
                add_filter(array('ElementForm', 'Item', $element->set_name, $element->name ), array($this, 'elementFormFilter'), 2);
                add_filter(array('ElementInput', 'Item', $element->set_name, $element->name ), array($this, 'elementInputFilter'), 2);
            }
        }
    }
    /**
     * Contribution install hook
     */
    public function hookInstall()
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
            `long_text` BOOLEAN DEFAULT TRUE,
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
    public function hookUninstall()
    {
        // Delete all the Contribution options
        foreach ($this->_options as $option) {
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
        if(version_compare($oldVersion, '3.0', '<')) {
            $sql = "ALTER TABLE `{$this->_db->ContributionTypeElement}` ADD `long_text` BOOLEAN DEFAULT TRUE";
            $this->_db->query($sql);

            $contributionTypeElements = $this->_db->getTable('ContributionTypeElement')->findAll();
            foreach($contributionTypeElements as $typeElement) {
                $typeElement->long_text = true;
                $typeElement->save();
            }

            //clean up contributed item records if the corresponding item has been deleted
            //earlier verison of the plugin did not use the delete hook
            $sql = "DELETE  FROM `{$this->_db->ContributionContributedItem}` WHERE NOT EXISTS (SELECT 1 FROM `{$this->_db->prefix}items`  WHERE `{$this->_db->prefix}contribution_contributed_items`.`item_id` = `{$this->_db->prefix}items`.`id`)";
           
            $this->_db->query($sql);           
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
        $acl->addResource('Contribution_Contributors');
        $acl->addResource('Contribution_ContributorMetadata');
        $acl->addResource('Contribution_Types');
        $acl->addResource('Contribution_Settings');
        
        $acl->allow(null, 'Contribution_Contribution', array('contribute', 'type-form', 'thankyou'));
        
        
        $acl->addResource('Contribution_ContributorField');
        $acl->addResource('Contribution_ContributorValue');
        
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
        
        if (is_admin_theme()) {
        $router->addRoute('contributionAdmin',
            new Zend_Controller_Router_Route('contribution/:controller/:action/*',
                array('module' => 'contribution',
                      'controller' => 'index',
                      'action'     => 'index'
                        )));
        }
    }

    /**
     * Append a Contribution entry to the admin navigation.
     *
     * @param array $nav
     * @return array
     */
    public function filterAdminNavigationMain($nav)
    {
        if(is_allowed('Contribution_Contributors', 'browse')) {
            
            $nav['Contribution'] = array('label'=>'Contribution', 'uri' => url('contribution/index'));
        }
        return $nav;
    }
    
    public function filterApiResources($apiResources)
    {
        $apiResources['contribution_contributed_items'] = array(
                'record_type' => 'ContributionContributedItem',
                'actions' => array('get', 'index'),
                );

        $apiResources['contribution_contributors'] = array(
            'record_type' => 'ContributionContributor',
            'actions' => array('get', 'index'),
            );

        $apiResources['contribution_contributor_fields'] = array(
                'record_type' => 'ContributionContributorField',
                'actions' => array('get', 'index'),
                );
        
        $apiResources['contribution_contributor_values'] = array(
            'record_type' => 'ContributionContributorValue',
            'actions' => array('get', 'index'),
            'index_params' => array('contributor_id')
            );

        $apiResources['contribution_types'] = array(
            'record_type' => 'ContributionType',
            'actions' => array('get', 'index'),
            );
        
        $apiResources['contribution_type_elements'] = array(
            'record_type' => 'ContributionTypeElement',
            'actions' => array('get', 'index'),
            );
        return $apiResources;
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
        'visible' => true
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
    
    public function filterApiImportOmekaAdapters($adapters, $args)
    {
        if ( (strpos($args['endpointUri'], 'omeka.net') !== false)
            || (strpos($args['endpointUri'], 'omeka-staging.net') !== false) ) {

            $contributionContributorAdapter = 
                new ApiImport_ResponseAdapter_OmekaNet_ContributorsAdapter(null, $args['endpointUri'], 'ContributionContributor');
            $adapters['contribution_contributors'] = $contributionContributorAdapter;
            
            $contributedItemAdapter = 
                new ApiImport_ResponseAdapter_Omeka_GenericAdapter(null, $args['endpointUri'], 'ContributionContributedItem');
            $contributedItemAdapter->setResourceProperties(
                    array('item' => 'Item',
                          'contributor' => 'ContributionContributor'
                            ));
            $adapters['contribution_contributed_items'] = $contributedItemAdapter;
            
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

            $contributionFieldAdapter = 
                new ApiImport_ResponseAdapter_Omeka_GenericAdapter(null, $args['endpointUri'], 'ContributionContributorField');
            $adapters['contribution_contributor_fields'] = $contributionFieldAdapter;
            
            
            $contributionValueAdapter = 
                new ApiImport_ResponseAdapter_Omeka_GenericAdapter(null, $args['endpointUri'], 'ContributionContributorValue');
            $contributionValueAdapter->setResourceProperties(
                    array('contributor' => 'ContributionContributor',
                          'field'       => 'ContributionContributorField'
                            ));
            $adapters['contribution_contributor_values'] = $contributionValueAdapter;
            
            return $adapters;
        }
        return $adapters;
    }
        
    /**
     * Append Contribution search selectors to the advanced search page.
     *
     * @return string HTML
     */
    public function hookAdminItemsSearch($args)
    {
        $view = get_view();
        $html = '<div class="field">';
        $html .= '<div class="two columns alpha">';
        $html .= $view->formLabel('contributed', 'Contribution Status');
        $html .= '</div>';
        $html .= '<div class="five columns omega inputs">';
        $html .= $view->formSelect('contributed', null, null, array(
           ''  => 'Select Below',
           '1' => 'Only Contributed Items',
           '0' => 'Only Non-Contributed Items'
        ));
        $html .= '</div></div>';
        echo $html;
    }
    
    /**
     * Append Contribution search selectors to the advanced search page.
     *
     * @return string HTML
     */
    public function hookPublicItemsSearch($args)
    {
        $view = get_view();
        $html = '<div class="field">';
        $html .= '<div class="two columns alpha">';
        $html .= $view->formLabel('contributed', 'Contribution Status');
        $html .= '</div>';
        $html .= '<div class="five columns omega inputs">';
        $html .= $view->formSelect('contributed', null, null, array(
           ''  => 'Select Below',
           '1' => 'Only Contributed Items',
           '0' => 'Only Non-Contributed Items'
        ));
        $html .= '</div></div>';
        echo $html;
    }

    public function hookAfterDeleteItem($args)
    {
        $item = $args['record'];
        $contributionItem = $this->_db->getTable('ContributionContributedItem')->findByItem($item);
        if($contributionItem) {
            $contributionItem->delete();
        }
    }

    public function hookAdminItemsShowSidebar($args)
    {
        $item = $args['item'];
        if ($contributor = contribution_get_item_contributor($item)) {
            if (!($name = contributor('Name', $contributor))) {
                $name = 'Anonymous';
            }
            $id = contributor('ID', $contributor);
            $url = url('contribution/contributors/show/id/') . $id;
            $publicMessage = contribution_is_item_public($item)
                           ? 'This item can be made public.'
                           : 'This item should not be made public.';
        ?>
<div class="panel">
    <h2>Contribution</h2>
    <p>This item was contributed by
        <a href="<?php echo $url; ?>"><?php echo $name; ?></a>.
    </p>
    <p><strong><?php echo $publicMessage; ?></strong></p>
</div>
<?php
        }
    }

    public function hookAdminItemsBrowseDetailedEach($args)
    {
        $item = $args['item'];
        if ($contributor = contribution_get_item_contributor($item)) {
            if (!($name = contributor('Name', $contributor))) {
                $name = 'Anonymous';
            }
            $id = contributor('ID', $contributor);
            $url = url('contribution/contributors/show/id/') . $id;
            $publicMessage = contribution_is_item_public($item)
                           ? 'This item can be made public.'
                           : 'This item should not be made public.';
        ?>
<h4>Contribution</h4>
<p>This item was contributed by
    <a href="<?php echo $url; ?>"><?php echo $name; ?></a>.
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
     * Prevent admins from accidentally making a privately contributed item public.
     * @param array $args
     */
    public function hookBeforeSaveItem($args)
    {
      $item = $args['record'];
      if($item->exists()) {
          //prevent admins from overriding the contributer's assertion of public vs private
          $contributionItem = $this->_db->getTable('ContributionContributedItem')->findByItem($item);
          if($contributionItem) {
              if(!$contributionItem->public && $item->public) {
                  $item->public = false;
                  Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger')->addMessage("Cannot override contributor's desire to leave contribution private", 'error');
              }
          }
      }
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
        if($contributionElement->long_text == 0) {
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
        $components['label'] = $prompt;
        $components['add_input'] = null;
        return $components;
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
        $textElement->long_text = 0;
        $textElement->save();
        $textElement = new ContributionTypeElement;
        $textElement->type_id = $storyType->id;
        $textElement->element_id = 1;
        $textElement->prompt = 'Story Text';
        $textElement->order = 2;
        $textElement->long_text = 1;
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
        $descriptionElement->long_text = 1;
        $descriptionElement->save();
    }
}
