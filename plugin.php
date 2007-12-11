<?php 
require_once 'Contributor.php';
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
 * 
 *
 * The text of the 'rights' field is stored in the themes/contribution/consent.php file, and it should be edited for each project.
 *
 * @author CHNM
 * @version $Id$
 * @copyright CHNM, 11 October, 2007
 * @package Contribution
 **/

function strip_tags_recursive($input)
{
	return is_array($input) ?  array_map('strip_tags_recursive', $input) : strip_tags($input);
}

if(get_magic_quotes_gpc()) {
	$_POST = stripslashes_deep($_POST);
}

require_once 'Contributor.php';

define('CONTRIBUTION_PLUGIN_VERSION', 0.1);

add_plugin_hook('initialize', 'contribution_initialize');

add_plugin_hook('add_routes', 'contribution_routes');

function contribution_initialize()
{
	add_controllers('controllers');
	add_theme_pages('theme', 'public');
	add_theme_pages('admin', 'admin');
	add_navigation('Contributors', 'contribution/contributors', 'main', array('Entities','add'));
}

function contribution_routes($router)
{
	$router->addRoute('contribute', new Zend_Controller_Router_Route('contribute/', array('controller'=>'index', 'action'=>'add', 'module'=>'contribution')));
	
	$router->addRoute('contribute_actions', new Zend_Controller_Router_Route('contribution/:action', array('controller'=>'index', 'module'=>'contribution', 'action'=>'add')));
}

add_plugin_hook('append_to_item_show', 'contribution_show_info');

function contribution_show_info($item)
{
	include 'show.php';
}

add_plugin_hook('append_to_item_form', 'contribution_edit_info');

function contribution_edit_info($item)
{
	include 'form.php';
}

//We need a hook to actually save the input from contribution_edit_info()

add_plugin_hook('save_item', 'contribution_save_info');

function contribution_save_info($item)
{
	if(isset($_POST['posting_consent'])) {
		$item->setMetatext('Posting Consent', $_POST['posting_consent']);
	}
	
	if(isset($_POST['submission_consent'])) {
		$item->setMetatext('Submission Consent', $_POST['submission_consent']);
	}
	
	$item->saveMetatext();
}

add_plugin_hook('install', 'contribution_install');

function contribution_install()
{	
	define_metafield('Online Submission', 'Indicates whether or not this Item has been contributed from a front-end contribution form.');
	
	define_metafield('Posting Consent', 'Indicates whether or not the contributor of this Item has given permission to post this to the archive. (Yes/No)');
	
	define_metafield('Submission Consent', 'Indicates whether or not the contributor of this Item has given permission to submit this to the archive. (Yes/No)');
	
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
		
	set_option('contribution_plugin_version', CONTRIBUTION_PLUGIN_VERSION);
	
}

add_plugin_hook('config_form', 'contribution_config_form');
add_plugin_hook('config', 'contribution_config');

function contribution_config_form()
{
	?>
	<label for="contributor_email">Contributor 'From' Email Address:</label><p class="instructionText">Please enter the email address that you would like to appear in the 'From' field for all notification emails for new contributions.  Leave this field blank if you would not like to email a contributor whenever he/she makes a new contribution:</p>
	<input type="text" name="contributor_email" value="<?php settings('contribution_notification_email'); ?>" />
<?php
}

function contribution_config($post)
{
	set_option('contribution_notification_email', $post['contributor_email']);
}

add_plugin_hook('delete_entity', 'contribution_delete_contributor');
/**
 * We want to delete the contributor associated with any entities that will be deleted
 *
 * @return void
 **/
function contribution_delete_contributor($entity)
{
	$entity_id = $entity->id;
	
	$contributor = Doctrine_Manager::getInstance()->getTable('Contributor')->findByEntity_id($entity_id);
	
	if(!$contributor) return;
	
	$contributor->delete();			
}

function contribution_partial()
{
	$partial = Zend_Registry::get( 'contribution_partial' );
	common($partial, array('data'=>$_POST), 'contribution'); 
}
 
function contribution_url($return = false)
{
	$url = generate_url(array('controller'=>'contribution','action'=>'add'), 'contribute');
	if($return) return $url;
	echo $url;
}

function link_to_contribute($text, $options = array())
{
	echo '<a href="' . contribution_url(true) . '" ' . _tag_attributes($options) . ">$text</a>";
}

function submission_consent($item)
{
	return $item->getMetatext('Submission Consent');
}

function submitted_through_contribution_form($item)
{
	return ($item->getMetatext('Online Submission') == 'Yes');
}

function contribution_is_anonymous($item)
{
	return ($item->getMetatext('Posting Consent') == 'Anonymously');
}

?>
