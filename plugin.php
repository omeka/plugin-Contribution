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
define('CONTRIBUTION_MIGRATION', 1);
define('CONTRIBUTION_PAGE_PATH', 'contribution/');

add_plugin_hook('define_routes', 'contribution_routes');
add_plugin_hook('config_form', 'contribution_config_form');
add_plugin_hook('config', 'contribution_config');
add_plugin_hook('before_update_item', 'contribution_save_info');
// temporarily commented out
//add_plugin_hook('append_to_item_show', 'contribution_show_info');
//add_plugin_hook('append_to_item_form', 'contribution_edit_info');
add_plugin_hook('install', 'contribution_install');
add_plugin_hook('initialize', 'contribution_initialize');
add_plugin_hook('define_acl', 'contribution_acl');

add_filter('public_navigation_main', 'contribution_public_main_nav');
add_filter('admin_navigation_main', 'contribution_admin_nav');

function contribution_routes($router)
{
	// get the base path
	$bp = get_option('contribution_page_path');

    $router->addRoute('contributionAdd', new Zend_Controller_Router_Route($bp, array('module' => 'contribution', 'controller'=> 'index', 'action'=>'add')));
    
	$router->addRoute('contributionLinks', new Zend_Controller_Router_Route($bp . ':action', array('module' => 'contribution', 'controller'=> 'index')));    
}

function contribution_show_info($item)
{
	include 'show.php';
}

function contribution_edit_info($item)
{
	include 'form.php';
}

//We need a hook to actually save the input from contribution_edit_info()

function contribution_save_info($item)
{
	if(isset($_POST['posting_consent'])) {
		$item->setMetatext('Posting Consent', $_POST['posting_consent']);
	}
	
	if(isset($_POST['submission_consent'])) {
		$item->setMetatext('Submission Consent', $_POST['submission_consent']);
	}	
}

function contribution_install()
{	
	$db = get_db();
	
	contribution_build_element_set(true);

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
		
	set_option('contribution_plugin_version', CONTRIBUTION_PLUGIN_VERSION);
	set_option('contribution_page_path', CONTRIBUTION_PAGE_PATH);
	set_option('contribution_require_tos_and_pp', FALSE);
	set_option('contribution_db_migration', CONTRIBUTION_MIGRATION);
}

function contribution_build_element_set($buildElements=true)
{
    try {
        $elementSet = new ElementSet;
	    $elementSet->name = "Contribution Form";
	    $elementSet->description = "The set of elements containing metadata from the Contribution form.";

	    if ($buildElements) {
	        $elementSet->addElements(array(
	             array(
	                'name'=>'Online Submission',
	                'description'=>'Indicates whether or not this Item has been contributed from a front-end contribution form.',
	                'record_type'=>'Item'),
	             array(
	                 'name'=>'Posting Consent',
	                 'description'=>'Indicates whether or not the contributor of this Item has given permission to post this to the archive. (Yes/No)',
	                 'record_type'=>'Item'),
	             array(
	                 'name'=>'Submission Consent',
	                 'description'=>'Indicates whether or not the contributor of this Item has given permission to submit this to the archive. (Yes/No)',
	                 'record_type'=>'Item')));
	    }

	    // Die if this doesn't save properly.
	    $elementSet->forceSave();
    } catch (Exception $e) {
        var_dump($e);exit;
    }
    return $elementSet->id;
}

function contribution_convert_existing_elements()
{
    // Retrieve the existing elements and modify them to belong to the Contribution Form element set.
    $db = get_db();
    $sql  = "SELECT id FROM $db->ElementSet WHERE name = 'Additional Item Metadata'";
    $additionalItemElementSetId = $db->fetchOne($sql);

    // If the Additional Item Metadata element set does not exist for whatever
    // reason, there is no pre-existing plugin data to convert so just build
    // the whole thing from scratch.  Otherwise just make the new element set
    // w/o the new elements and convert the old ones.
    $newElementSetId = contribution_build_element_set(!$additionalItemElementSetId);

    // Update the existing elements w/o interacting with the ElementSet models.
    try {
        $db->query(
	            "UPDATE $db->Element SET element_set_id = ? 
	            WHERE element_set_id = ? AND name IN (" . $db->quote(
	                array('Online Submission', 'Posting Consent', 'Submission Consent')) .
	            ") LIMIT 3",
	            array($newElementSetId, $additionalItemElementSetId));
    } catch (Exception $e) {
        var_dump($e);exit;
    }
}

function contribution_config_form()
{
	$textInputSize = 30;
	$textAreaRows = 10;
	$textAreaCols = 50;
	?>
	
	<label for="contribution_page_path">Relative Page Path From Project Root:</label>
	<p class="instructionText">Please enter the relative page path from the project root where you want the contribution page to be located. Use forward slashes to indicate subdirectories, but do not begin with a forward slash.</p>
	<input type="text" name="contribution_page_path" value="<?php echo settings('contribution_page_path'); ?>" size="<?php echo $textInputSize; ?>" />
	
	<label for="contributor_email">Contributor 'From' Email Address:</label>
	<p class="instructionText">Please enter the email address that you would like to appear in the 'From' field for all notification emails for new contributions.  Leave this field blank if you would not like to email a contributor whenever he/she makes a new contribution:</p>
	<input type="text" name="contributor_email" value="<?php settings('contribution_notification_email'); ?>" size="<?php echo $textInputSize; ?>" />

	<label for="contribution_consent_text">Consent Text:</label>
	<p class="instructionText">Please enter the legal text of your consent form:</p>				
	<textarea id="contribution_consent_text" name="contribution_consent_text" rows="<?php echo $textAreaRows; ?>" cols="<?php echo $textAreaCols; ?>"><?php echo settings('contribution_consent_text'); ?></textarea>
	
	<?php if ( function_exists('terms_of_service_link_tos')):?>
	
		<label for="contribution_require_tos_and_pp">Require Terms of Service and Privacy Policy</label>
		<p class="instructionText">Please check whether you want to require contributors to agree to the Terms of Service and Privacy Policy.</p>				
		<?php checkbox(array('name'=> 'contribution_require_tos_and_pp', 'id'=> 'contribution_require_tos_and_pp'),  get_option('contribution_require_tos_and_pp'), null, null); ?>
	
	<?php endif;?>
	
<?php
}

function contribution_config($post)
{
	set_option('contribution_consent_text', $post['contribution_consent_text']);
	set_option('contribution_notification_email', $post['contributor_email']);
	set_option('contribution_page_path', $post['contribution_page_path']);
    // set_option('contribution_page_path',  trim($post['contribution_page_path'], '/') . '/');
	set_option('contribution_require_tos_and_pp', strtolower($post['contribution_require_tos_and_pp']) == 'on');
	
	//if the page path is empty then make it the default page path
	if (trim(get_option('contribution_page_path')) == '') {
		set_option('contribution_page_path', CONTRIBUTION_PAGE_PATH);
	}	
}

function contribution_link_to_contribute($text, $options = array())
{
	echo '<a href="' . uri(array(), 'contributionAdd') . '" ' . _tag_attributes($options) . ">$text</a>";
}

function contribution_submission_consent($item)
{
	return $item->getMetatext('Submission Consent');
}

function contribution_embed_consent_form() {
?>
	<form action="<?php echo uri(array('action'=>'submit'), 'contributionLinks'); ?>" id="consent" method="post" accept-charset="utf-8">

			<h3>Please read this carefully:</h3>
			
			<div id="contribution_consent">
				<p><?php echo settings('contribution_consent_text'); ?></p>
				
				<?php if (get_option('contribution_require_tos_and_pp') && function_exists('terms_of_service_link_tos')): ?>
					<p>You understand and agree to the <?php echo terms_of_service_link_tos('Terms of Service'); ?> and <?php echo terms_of_service_link_privacy_policy('Privacy Policy'); ?>.</p>
				<?php endif; ?>
				
				<textarea name="contribution_consent_text" style="display:none;"><?php echo settings('contribution_consent_text'); ?></textarea>
			</div>
			
			<div class="field">
				<p>Please give your consent below</p>
				<div class="radioinputs"><?php radio(array('name'=>'contribution_submission_consent'), 
						array(	'Yes'		=> ' I Agree. Please include my contribution.',
								'No'		=> ' No, I do not agree.'), 'No'); ?></div>
			</div>
			
	
		<input type="submit" class="submitinput" name="submit" value="Submit" />
	</form>
<?php
}

function contribution_submitted_through_contribution_form($item)
{
	return ($item->getMetatext('Online Submission') == 'Yes');
}

function contribution_is_anonymous($item)
{
	return ($item->getMetatext('Posting Consent') == 'Anonymously');
}

function contribution_admin_nav($navArray) 
{
    if (has_permission('Contribution_Index', 'browse')) {
        $navArray += array('Contributors'=> uri(array('action'=>'browse'), 'contributionLinks'));
    }
    return $navArray;
}

function contribution_public_main_nav($navArray) {
    $navArray['Contribute'] = uri(array(), 'contributionAdd');
    return $navArray;
}

/**
 * Use this initialize hook to check to see whether or not we need to upgrade the plugin.
 * 
 * @param string
 * @return void
 **/
function contribution_initialize()
{
    contribution_upgrade();
}

function contribution_upgrade()
{
    $pluginVersion = get_option('contribution_db_migration');
    if ($pluginVersion < CONTRIBUTION_MIGRATION) {
        contribution_convert_existing_elements();
        // Bump up the database's migration #
        set_option('contribution_db_migration', CONTRIBUTION_MIGRATION);
    }
}

function contribution_acl($acl)
{
    $acl->loadResourceList(array('Contribution_Index'=>array('browse', 'edit', 'delete')));
}

/**
 * A prototype of the insert_item() helper, which will be in the core in 1.0.
 *
 * @param array $itemMetadata Array which can include the following properties:
 *      'public' (boolean)
 *      'featured' (boolean)
 *      'collection_id' (integer)
 *      'item_type_name' (string)
 *      'tags' (string, comma-delimited)
 *      'tag_entity' (Entity, optional and only checked if 'tags' is given)
 * @param array $elementTexts Array of element texts to assign to the item.  This
 * takes the format: array('Element Set Name'=>array('Element Name'=>array(array('text'=>(string), 'html'=>(boolean))))).
 * @return Item
 * @throws Omeka_Validator_Exception
 **/
function contribution_insert_item($itemMetadata = array(), $elementTexts = array())
{
    // Insert a new Item
    $item = new Item;

    // Item Metadata
    $item->public           = $itemMetadata['public'];
    $item->featured         = $itemMetadata['featured'];
    $item->collection_id    = $itemMetadata['collection_id'];

    if (array_key_exists('item_type_name', $itemMetadata)) {
        $itemType = get_db()->getTable('ItemType')->findBySql('name = ?', array($itemMetadata['item_type_name']), true);

        if(!$itemType) {
            throw new Omeka_Validator_Exception( "Invalid type named {$_POST['type']} provided!");
        }

        $item->item_type_id = $itemType->id;
    }

    foreach ($elementTexts as $elementSetName => $elements) {
        foreach ($elements as $elementName => $elementTexts) {
            $element = $item->getElementByNameAndSetName($elementName, $elementSetName);
            foreach ($elementTexts as $elementText) {
                if (!array_key_exists('text', $elementText)) {
                    throw new Exception('Element texts are formatted incorrectly for insert_item()!');
                }
                $item->addTextForElement($element, $elementText['text'], $elementText['html']);
            }
        }
    }

    // Save Item and all of its metadata.  Throw exception if it fails.
    $item->forceSave();

    // Add tags for the item.
    if (array_key_exists('tags', $itemMetadata) and !empty($itemMetadata['tags'])) {
        // As of 0.10 we still need to tag for a specific entity.
        // This may change in future versions.
        $entityToTag = array_key_exists('tag_entity', $itemMetadata) ?
            $itemMetadata['tag_entity'] : current_user()->Entity;
        $item->addTags($itemMetadata['tags'], $entityToTag);
    }

    // Save Element Texts (necessary)
    $item->saveElementTexts();

    return $item;
}

/**
 * @see contribution_add_item()
 * @param Item|int $item Either an Item object or the ID for the item.
 * @param array $itemMetadata Set of options that can be passed to the item.  This
 * has a few options that are different from contribution_add_item():
 *      'overwriteElementTexts' (boolean) -- determines whether or not to overwrite
 * existing element texts.  If true, this will loop through the element texts
 * provided in $elementTexts, and it will update existing records where possible.
 * All texts that are not yet in the DB will be added in the usual manner.  
 * False by default.
 *      
 * @param array $elementTexts
 * @return Item
 **/
function contribution_update_item($item, $itemMetadata = array(), $elementTexts = array())
{
    if (is_int($item)) {
        $item = get_db()->getTable('Item')->find($item);
    } else if (!($item instanceof Item)) {
        throw new Exception('$item must be either an Item record or the item ID!');
    }
    
    // If this option is set, it will loop through the $elementTexts provided,
    // find each one and manually update it (provided it exists).
    // The rest of the element texts will get added as per usual.
    if (array_key_exists('overwriteElementTexts', $itemMetadata)) {
        foreach ($elementTexts as $elementSetName => $textArray) {
            foreach ($textArray as $elementName => $elementTextSet) {
                $etRecordSet = $item->getElementTextsByElementNameAndSetName($elementName, $elementSetName);
                foreach ($elementTextSet as $elementTextIndex => $textAttr) {
                    // If we have an existing ElementText record, use that
                    // instead of adding a new one.
                    if (array_key_exists($elementTextIndex, $etRecordSet)) {
                        $etRecord = $etRecordSet[$elementTextIndex];
                        $etRecord->text = $textAttr['text'];
                        $etRecord->html = $textAttr['html'];
                        $etRecord->forceSave();
                    } else {
                        // Otherwise we should just append the new text to the 
                        // pre-existing ones.
                        $elementRecord = $item->getElementByNameAndSetName($elementName, $elementSetName);
                        $item->addTextForElement($elementRecord, $textAttr['text'], $textAttr['html']);
                    }
                }
            }
        }
    }
    
    $item->saveElementTexts();
    
    return $item;
}