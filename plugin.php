<?php 
/**
 * Notes on correct usage:
 * This plugin will not work correctly if one or more of the following item Types has been removed:
 *		Document
 *		Still Image
 *		Moving Image
 * 		Sound
 *
 * Also, it will not work correctly if the Document type does not have a metafield called Text,
 * which is a default setting in Omeka.  This is because the "story" for the item is stored in the Text field of a Document.
 *
 * The text of the 'rights' field is stored in the views/public/contribution/consent.php file, and it should be edited for each project.
 *
 * @author CHNM
 * @version $Id$
 * @copyright CHNM, 2007-2008
 * @package Contribution
 **/

define('CONTRIBUTION_PLUGIN_VERSION', 0.2);
// Define this migration constant to help with upgrading the plugin.
define('CONTRIBUTION_MIGRATION', 2);
define('CONTRIBUTION_PAGE_PATH', 'contribution/');
define('CONTRIBUTORS_PER_PAGE', 10);

add_plugin_hook('define_routes', 'contribution_routes');
add_plugin_hook('config_form', 'contribution_config_form');
add_plugin_hook('config', 'contribution_config');
add_plugin_hook('install', array('ContributionUpgrader', 'install'));
add_plugin_hook('define_acl', 'contribution_acl');

add_filter('public_navigation_main', 'contribution_public_main_nav');
add_filter('admin_navigation_main', 'contribution_admin_nav');

add_filter(array('Form', 'Item', 'Contribution Form', 'Posting Consent'), 'contribution_posting_consent_form');
add_filter(array('Form', 'Item', 'Contribution Form', 'Submission Consent'), 'contribution_submission_consent_form');
add_filter(array('Form', 'Item', 'Contribution Form', 'Online Submission'), 'contribution_is_online_submission_form');
add_filter(array('Form', 'Item', 'Contribution Form', 'Contributor is Creator'), 'contribution_creator_is_contributor_form');

add_filter(array('Display', 'Item', 'Dublin Core', 'Contributor'), 'contribution_display_anonymous');
add_filter(array('Display', 'Item', 'Dublin Core', 'Creator'), 'contribution_display_anonymous');

function contribution_routes($router)
{
	// get the base path
	$bp = get_option('contribution_page_path');

    $router->addRoute('contributionAdd', new Zend_Controller_Router_Route($bp, array('module' => 'contribution', 'controller'=> 'index', 'action'=>'add')));
    
	$router->addRoute('contributionLinks', new Zend_Controller_Router_Route($bp . ':action', array('module' => 'contribution', 'controller'=> 'index')));    
}

function contribution_config_form()
{
    // Deal with upgrading the plugin if necessary.
    $pluginVersion = (int)get_option('contribution_db_migration');
    // Skip the migrations if we don't need it.
    if ($pluginVersion < CONTRIBUTION_MIGRATION) {
        ContributionUpgrader::upgrade($pluginVersion, CONTRIBUTION_MIGRATION);
    }    
    
    
	$textInputSize = 30;
	$textAreaRows = 10;
	$textAreaCols = 50;
	?>
	
	<div class="field">
	<label for="contribution_page_path">Relative Page Path From Project Root:</label>
	<div class="inputs">
	    <input type="text" name="contribution_page_path" value="<?php echo settings('contribution_page_path'); ?>" size="<?php echo $textInputSize; ?>" />
    	<p class="explanation">Please enter the relative page path from the project root where you want the contribution page to be located. Use forward slashes to indicate subdirectories, but do not begin with a forward slash.</p>
	</div>
	</div>
	
	<div class="field">
	<label for="contributor_email">Contributor 'From' Email Address:</label>
	<div class="inputs">
	    <input type="text" name="contributor_email" value="<?php echo settings('contribution_notification_email'); ?>" size="<?php echo $textInputSize; ?>" />
    	<p class="explanation">Please enter the email address that you would like to appear in the 'From' field for all notification emails for new contributions.  Leave this field blank if you would not like to email a contributor whenever he/she makes a new contribution:</p>
	</div>
    </div>
    
    <div class="field">
	<label for="contribution_consent_text">Consent Text:</label>
	<div class="inputs">
	    <textarea id="contribution_consent_text" name="contribution_consent_text" rows="<?php echo $textAreaRows; ?>" cols="<?php echo $textAreaCols; ?>"><?php echo settings('contribution_consent_text'); ?></textarea>
    	<p class="explanation">Please enter the legal text of your consent form:</p>
	</div>
	</div>
	
	<div class="field">
	<label for="recaptcha_public_key">reCAPTCHA Public Key</label>
	<div class="inputs">
	    <input type="text" name="recaptcha_public_key" value="<?php echo settings('contribution_recaptcha_public_key') ?>" id="recaptcha_public_key" />
	    <p class="explanation">To enable CAPTCHA for the contribution form, please obtain a <a href="http://recaptcha.net/">ReCAPTCHA</a> API key and enter the relevant values.</p>
	</div>
	</div>
	
	<div class="field">
	<label for="recaptcha_private_key">reCAPTCHA Private Key</label>
	<div class="inputs">
	    <input type="text" name="recaptcha_private_key" value="<?php echo settings('contribution_recaptcha_private_key') ?>" id="recaptcha_private_key" />
	</div>
	</div>
<?php
}

function contribution_config($post)
{
    set_option('contribution_recaptcha_public_key', $_POST['recaptcha_public_key']);
    set_option('contribution_recaptcha_private_key', $_POST['recaptcha_private_key']);
	set_option('contribution_consent_text', $post['contribution_consent_text']);
	set_option('contribution_notification_email', $post['contributor_email']);
	set_option('contribution_page_path', $post['contribution_page_path']);
	set_option('contribution_require_tos_and_pp', (boolean)$post['contribution_require_tos_and_pp']);
	
	
	//if the page path is empty then make it the default page path
	if (trim(get_option('contribution_page_path')) == '') {
		set_option('contribution_page_path', CONTRIBUTION_PAGE_PATH);
	}	
}

function contribution_link_to_contribute($text, $options = array())
{
	echo '<a href="' . uri(array(), 'contributionAdd') . '" ' . _tag_attributes($options) . ">$text</a>";
}

function contribution_embed_consent_form() {
?>
	<form action="<?php echo uri(array('action'=>'submit'), 'contributionLinks'); ?>" id="consent" method="post" accept-charset="utf-8">

			<h3>Please read this carefully:</h3>
			
			<div id="contribution_consent">
				<p><?php echo settings('contribution_consent_text'); ?></p>

				<textarea name="contribution_consent_text" style="display:none;"><?php echo settings('contribution_consent_text'); ?></textarea>
			</div>
			
			<div class="field">
				<p>Please give your consent below</p>
				<div class="radioinputs"><?php echo radio(array('name'=>'contribution_submission_consent'), 
						array(	'Yes'		=> ' I Agree. Please include my contribution.',
								'No'		=> ' No, I do not agree.'), 'No'); ?></div>
			</div>
			
	
		<input type="submit" class="submitinput" name="submit" value="Submit" />
	</form>
<?php
}

/**
 * Determine whether or not the user contribution is supposed to be considered
 * anonymous.
 * 
 * @param Item $item
 * @return boolean
 **/
function contribution_is_anonymous($item)
{
    return 'Anonymously' == item('Contribution Form', 'Posting Consent', array(), $item);
}

function contribution_admin_nav($navArray) 
{
    if (has_permission('Contribution_Index', 'browse')) {
        // This section of the admin should use the default routing construction
        // mechanism in ZF, because otherwise pagination_links() will not recognize
        // the 'page' routing parameter that is in the pagination control.
        $navArray += array('Contributors'=> uri(array('module'=>'contribution', 'controller'=>'index', 'action'=>'browse'), 'default'));
    }
    return $navArray;
}

function contribution_public_main_nav($navArray) {
    $navArray['Contribute'] = uri(array(), 'contributionAdd');
    return $navArray;
}

function contribution_acl($acl)
{
    $acl->loadResourceList(array('Contribution_Index'=>array('browse', 'edit', 'delete')));
}

function contribution_posting_consent_form($html, $inputNameStem, $consent, $options, $item, $element)
{
    return __v()->formSelect($inputNameStem . '[text]', $consent, null, array(''=>'Not Applicable', 'Yes'=>'Yes', 'No'=>'No', 'Anonymously'=>'Anonymously'));
}

function contribution_submission_consent_form($html, $inputNameStem, $consent, $options, $item, $element)
{
    return __v()->formSelect($inputNameStem . '[text]', $consent, null, array(''=>'Not Applicable', 'No'=>'No', 'Yes'=>'Yes'));
}

function contribution_creator_is_contributor_form($html, $inputNameStem, $isSame, $options, $item, $element)
{
    return __v()->formSelect($inputNameStem . '[text]', $isSame, null, array(''=>'Not Applicable', 'No'=>'No', 'Yes'=>'Yes'));
}

function contribution_is_online_submission_form($html, $inputNameStem, $consent, $options, $item, $element)
{
    return __v()->formSelect($inputNameStem . '[text]', $consent, null, array('No'=>'No', 'Yes'=>'Yes'));
}

/**
 * Display the provided name or 'Anonymous', depending on whether the item in
 * question was flagged as anonymous.
 * 
 * Also, if logged in to the admin theme, this will always allow the user to
 * see the original name.
 * 
 * @param string $name
 * @param Item $item
 * @return string Either the original $name or 'Anonymous'.
 **/
function contribution_display_anonymous($name, $item)
{    
    return (contribution_is_anonymous($item) && !is_admin_theme()) ? 'Anonymous' : $name;
}

/**
 * Encapsulates the handling of installation/upgrade of the Contribution plugin.
 *
 * @package Contribution
 * @copyright Center for History and New Media, 2009
 **/
class ContributionUpgrader
{    
    static protected $_elementsInSet = array(
         'Online Submission' => array(
            'name'=>'Online Submission',
            'description'=>'Indicates whether or not this Item has been contributed from a front-end contribution form.',
            'record_type'=>'Item'),
         'Posting Consent' => array(
             'name'=>'Posting Consent',
             'description'=>'Indicates whether or not the contributor of this Item has given permission to post this to the archive. (Yes/No)',
             'record_type'=>'Item'),
         'Submission Consent' => array(
             'name'=>'Submission Consent',
             'description'=>'Indicates whether or not the contributor of this Item has given permission to submit this to the archive. (Yes/No)',
             'record_type'=>'Item'),
         'Contributor is Creator' => array(
             'name'=>'Contributor is Creator',
             'description'=>'Indicates whether or not the contributor of the Item is responsible for its creation.',
             'record_type'=>'Item'
             ));
    
    /**
     * Install the Contribution plugin.
     * 
     * Create the 'Contribution Form' element set with 4 elements.  Create the
     * 'contributors' table in the database.  Set all the appropriate options
     * to the default values.
     * 
     * @return void
     **/
    public static function install()
    {
    	$elementSet = self::_insertElementSet();
        self::_addElementsToElementSet($elementSet);
        self::_addContributorsTable();
    	
    	set_option('contribution_plugin_version', CONTRIBUTION_PLUGIN_VERSION);
    	set_option('contribution_page_path', CONTRIBUTION_PAGE_PATH);
    	set_option('contribution_require_tos_and_pp', FALSE);
    	set_option('contribution_db_migration', CONTRIBUTION_MIGRATION);
    }
    
    /**
     * Upgrade the Contribution plugins from an existing version to another.
     * 
     * @param integer
     * @param integer
     * @return void
     **/
    public static function upgrade($from, $to)
    {
        $upgrader = new self;
        
        $currentMigration = $from;
        while ($currentMigration < $to) {
            $migrateMethod = '_to' . ++$currentMigration;
            $upgrader->$migrateMethod();
        }

        set_option('contribution_db_migration', $to);
    }
    
    /**
     * Upgrade the Contribution plugin database from its initial version.
     * 
     * In this case, '1' represents the first database migration and not the 
     * initial version of the plugin.  Performs the following tasks:
     * 
     * 1) Add the 'Contribution Form' element set.
     * 2) The existing elements would have fallen under the 'Additional Item
     * Metadata' element set, so if that set exists, then move all those elements
     * to the 'Contribution Form' set.
     * 3) Otherwise, add the elements as normal.
     * 
     * @return void
     **/
    private function _to1()
    {
        // Retrieve the existing elements and modify them to belong to the Contribution Form element set.
        $db = get_db();
        $sql  = "SELECT id FROM $db->ElementSet WHERE name = 'Additional Item Metadata'";
        $additionalItemElementSetId = $db->fetchOne($sql);

        // If the Additional Item Metadata element set does not exist for whatever
        // reason, there is no pre-existing plugin data to convert so just build
        // the whole thing from scratch.  Otherwise just make the new element set
        // w/o the new elements and convert the old ones.
        $contributionFormElementSet = self::_insertElementSet();
        if (!$additionalItemElementSetId) {
            // Add the elements from scratch
            self::_addElementsToElementSet($contributionFormElementSet);
        } else {
            // Otherwise, convert the existing elements.
            // Update the existing elements w/o interacting with the ElementSet models.
            try {
                $db->query(
        	            "UPDATE $db->Element SET element_set_id = ? 
        	            WHERE element_set_id = ? AND name IN (" . $db->quote(
        	                array('Online Submission', 'Posting Consent', 'Submission Consent')) .
        	            ") LIMIT 3",
        	            array($contributionFormElementSet->id, $additionalItemElementSetId));
            } catch (Exception $e) {
                var_dump($e);exit;
            }
        }
    }
    
    /**
     * The second database migration for the Contribution plugin.
     * 
     * This will add the 'Contribution is Creator' element and then populate it
     * with the 'Yes' flag for all the items that were submitted through the 
     * Contribution plugin that have the same 'Contributor' and 'Creator' field
     * values.
     * 
     * @return void
     **/
    private function _to2()
    {
        $this->_addContributorIsCreatorElement();
        $this->_fixItemsWhereContributorIsCreator();
    }
    
    /**
     * Add the 'Contributor is Creator' element to the Contribution Form 
     * element set.
     * 
     * If this element already exists, do nothing.
     * 
     * @return void
     **/
    private function _addContributorIsCreatorElement()
    {
        // Test to see whether or not we already have this element.
        $element = get_db()->getTable('Element')->findByElementSetNameAndElementName('Contribution Form', 'Contributor is Creator');
        if ($element) {
            return;
        }
        
        // Add the 'Contributor is Creator' element.
        $elementSet = $this->_getElementSet();
        $elementSet->addElements(array(self::$_elementsInSet['Contributor is Creator']));
        $elementSet->forceSave();
    }
    
    /**
     * Update all the items that were previously submitted through the Contribution
     * plugin so that they contain the correct 'Contributor is Creator' flag.
     * 
     * TODO: Stress test this on large sets.
     * @return void
     **/
    private function _fixItemsWhereContributorIsCreator()
    {
        // Migrate existing data for contributions.
        // If the item is listed as a user contribution (Online Submission = 'Yes')
        // And Dublin Core Creator = Dublin Core Contributor
        // Then UPDATE 'Contributor is Creator' = 'Yes'.
        
        $elementTable = get_db()->getTable('Element');
        $onlineSubmissionElementId = $elementTable->findByElementSetNameAndElementName('Contribution Form', 'Online Submission')->id;
        
        // var_dump(compact('creatorElementId', 'contributorElementId', 'onlineSubmissionElementId', 'postingConsentElementId'));exit;
        $criteria = array(array(
                            'terms'=>'Yes',
                            'type'=>'contains',
                            'element_id'=>$onlineSubmissionElementId));
        
        $itemSelectSql = get_db()->getTable('Item')->getSelectForFindBy(array('advanced_search'=>$criteria));
        
        // SELECT only the record IDs for items that have the same value for 
        // Dublin Core Contributor and Creator.
        // 
        // How we do this: select from the set of all Dublin Core Creators,
        // inner join that against the set of all Dublin Core Contributors 
        // (using the record_id), and then a simple equivalence test between
        // the retrieved creator and contributor values.
        $itemSelectSql->where("i.id IN (
        SELECT creator_set.record_id FROM (
            SELECT etx.id, etx.record_id, etx.text as creator FROM element_texts etx
            INNER JOIN elements e ON e.id = etx.element_id    
            INNER JOIN element_sets es ON es.id = e.element_set_id
            WHERE e.name = 'Creator' AND es.name = 'Dublin Core'    
        ) creator_set
        INNER JOIN (
            SELECT etx.id, etx.record_id, etx.text as contributor
            FROM element_texts etx
            INNER JOIN elements e ON e.id = etx.element_id    
            INNER JOIN element_sets es ON es.id = e.element_set_id
            WHERE e.name = 'Contributor' AND es.name = 'Dublin Core'
        ) contributor_set ON contributor_set.record_id = creator_set.record_id
        WHERE creator = contributor        
        )"); 
        
        // Get the total # of items to change
                
        // FIXME: Chunk this data retrieval so it doesn't die when iterating through
        // potentially thousands of items.
        
        $itemsToFix = get_db()->getTable('Item')->fetchObjects($itemSelectSql);
        
        foreach ($itemsToFix as $key => $item) {
            update_item($item, array(), 
            array('Contribution Form'=>
                array('Contributor is Creator'=>
                    array(array('text'=>'Yes', 'html'=>false)))));
            release_object($item);
        }
    }
     
    /**
     * Create the 'contributors' table in the database.
     *
     * @return void
     **/
    private static function _addContributorsTable()
    {
        $db = get_db();
        $db->exec("CREATE TABLE IF NOT EXISTS `$db->Contributor` (
    			`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    			`entity_id` BIGINT UNSIGNED NOT NULL ,
    			`birth_year` YEAR NULL,
    			`gender` TINYTEXT NULL,
    			`race` TINYTEXT NULL,
    			`occupation` TINYTEXT NULL,
    			`zipcode` TINYTEXT NULL,
    			`ip_address` TINYTEXT NOT NULL
    			) ENGINE = MYISAM ;");
    }
    
    /**
     * Retrieve the 'Contribution Form' element set object.
     * 
     * @return ElementSet|null
     **/
    private function _getElementSet()
    {
        $table = get_db()->getTable('ElementSet');
        $elementSetSelect = $table->getSelect()
                         ->where('es.name = ?', 'Contribution Form')
                         ->limit(1);
        return $table->fetchObject($elementSetSelect);
    }
    
    /**
     * Add the 'Contribution Form' element set to the database.
     * 
     * @return ElementSet
     **/
    private static function _insertElementSet()
    {
        $elementSet = new ElementSet;
        $elementSet->name = "Contribution Form";
        $elementSet->description = "The set of elements containing metadata from the Contribution form.";

        // Die if this doesn't save properly.
        $elementSet->forceSave();
        return $elementSet;        
    }
    
    /**
     * Adds all the default Contribution Form elements to an element set 
     * (should be the Contribution Form element set).
     * 
     * @param ElementSet $elementSet 
     * @return void
     **/
    private static function _addElementsToElementSet($elementSet)
    {
        $elementSet->addElements(self::$_elementsInSet);
        $elementSet->forceSave();
    }
}


