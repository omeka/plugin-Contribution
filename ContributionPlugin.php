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
    protected $_hooks = array(
        'install',
        'uninstall',
        'upgrade',
        'define_acl',
        'define_routes',
        'admin_append_to_plugin_uninstall_message',
        'admin_append_to_advanced_search',
        'admin_items_show_sidebar',
        'admin_items_browse_detailed_each',
        'item_browse_sql',
        'before_save_item'
    );

    protected $_filters = array(
        'admin_navigation_main',
        'public_navigation_main',
        'simple_vocab_routes',
        'item_citation'
        );

    protected $_options = array(
        'contribution_page_path',
        'contribution_email_sender',
        'contribution_email_recipients',
        'contribution_consent_text',
        'contribution_collection_id',
        'contribution_default_type'
    );

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
            `contributor_posting` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
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
        
        $sql = "ALTER TABLE `{$this->_db->prefix}contribution_contributed_items` ADD COLUMN `anonymous` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'";
    
        $this->_db->query($sql);
        
        $sql = "ALTER TABLE `{$this->_db->prefix}contribution_type_elements` ADD `long_text` BOOLEAN NULL";
        
        $this->_db->query($sql);
    }

    public function hookAdminAppendToPluginUninstallMessage()
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
        $acl->allow(array('super', 'admin'), 'Contribution_Contribution');
        $acl->allow(array('researcher', 'contributor', 'guest'), 'Contribution_Contribution', array('show', 'browse', 'contribute', 'thankyou', 'type-form', 'index'));
        
        
        $acl->addResource('Contribution_Contributors');
        $acl->addResource('Contribution_ContributorMetadata');
        $acl->addResource('Contribution_Types');
        $acl->addResource('Contribution_Settings');
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

            // get the base path
            $bp = get_option('contribution_page_path');

            if ($bp) {
                $router->addRoute('contributionCustom',
                    new Zend_Controller_Router_Route("{$bp}/:action/*",
                        array('module'     => 'contribution',
                              'controller' => 'contribution',
                              'action'     => 'contribute')));
            }else{
            
        $router->addRoute('contributionDefault',
              new Zend_Controller_Router_Route('contribution/:action/*',
                    array('module'     => 'contribution',
                          'controller' => 'contribution',
                          'action'     => 'contribute')));
            
            }
      
         if(is_admin_theme()){
            $router->addRoute('contributionAdmin',
                new Zend_Controller_Router_Route('contribution/:controller/:action/*',
                    array('module' => 'contribution',
                          'controller' => 'index',
                          'action' => 'index')));
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
           $nav[] = array(
                'label' => __('Contribution'),
                'uri' => url('contribution'),
                'resource' => 'Contribution_Contributors',
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
       //$nav['Contribute an Item'] = contribution_contribute_url();
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

    /**
     * Append Contribution search selectors to the advanced search page.
     *
     * @return string HTML
     */
    public function hookAdminAppendToAdvancedSearch()
    {
        $html = '<div class="field">';
        $html .= get_view()->formLabel('contributed', 'Contribution Status');
        $html .= '<div class="inputs">';
        $html .= get_view()->formSelect('contributed', null, null, array(
           ''  => 'Select Below',
           '1' => 'Only Contributed Items',
           '0' => 'Only Non-Contributed Items'
        ));
        $html .= '</div></div>';
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
    public function hookItemBrowseSql($args)
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
    
  public function hookBeforeSaveItem($args){
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

    
   public function filterItemCitation($cite,$args){
       $item = $args['item'];
       
       if(contribution_get_item_contributor($item)){
         $name = contribution_get_item_contributor($item);        

       if(contributor_option($item->id) < 1){       

        $creator    = $name->name;
       } else {
           $creator = "Anonymous";
       }
            $title      = metadata('item',array('Dublin Core', 'Title'));
            $siteTitle  = strip_formatting(option('site_title'));
            $itemId     = $item->id;
            $accessDate = date('F j, Y');
            $uri        = html_escape(record_url($item));

            $cite = '';
            if ($creator) {
                $cite .= "$creator, ";
            }
            if ($title) {
                $cite .= "&#8220;$title,&#8221; ";
            }
            if ($siteTitle) {
                $cite .= "<em>$siteTitle</em>, ";
            }
            $cite .= "accessed $accessDate, ";
            $cite .= "$uri.";
          
  
       }
       
       return $cite;
   }
   
    private function _adminBaseInfo($args) 
    {
        
        $item = $args['item'];
        $contributedItem = $this->_db->getTable('ContributionContributedItem')->findByItem($item);
        if($contributedItem) {
            $html = '';
            $name = $contributedItem->getContributor()->name;
            $html .= "<p><strong>" . __("Contributed by:") . "</strong><span class='contribution-contributor'> $name</span></p>";

            $publicMessage = '';
            if(is_allowed($item, 'edit')) {
                if($contributedItem->public) {
                    $publicMessage = __("This item can be made public.");
                } else {
                    $publicMessage = __("This item cannot be made public.");
                }
                $html .= "<p><strong>$publicMessage</strong></p>";
            }
            
            return $html;
        }
        
    }
    
   
   public function pluginOptions()
   {
        return $this->_options;
   }
}
