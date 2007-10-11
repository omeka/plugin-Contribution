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
 * 
 * This plugin currently uses 5 hooks: initialize, add_routes, append_to_page, install, and delete_entity
 *
 * The text of the 'rights' field is stored in the themes/contribution/consent.php file, and it should be edited for each project.
 *
 * @author CHNM
 * @version $Id$
 * @copyright CHNM, 11 October, 2007
 * @package Contribution
 **/

require_once 'Contributor.php';

define('CONTRIBUTION_PLUGIN_VERSION', 1);

add_plugin_hook('initialize', 'contribution_initialize');

add_plugin_hook('add_routes', 'contribution_routes');

function contribution_initialize()
{
	add_controllers('controllers');
	add_theme_pages('theme', 'public');
}

function contribution_routes($router)
{
	$router->addRoute('contribute', new Zend_Controller_Router_Route('contribute/', array('controller'=>'index', 'action'=>'add', 'module'=>'contribution')));
	
	$router->addRoute('contribute_actions', new Zend_Controller_Router_Route('contribution/:action', array('controller'=>'index', 'module'=>'contribution', 'action'=>'add')));
}

add_plugin_hook('append_to_page', 'contribution_append');

function contribution_append($page, $options)
{
	$item = $options['item'];
	
	if($page == 'items/show') {
		include 'show.php';
	}
}

add_plugin_hook('install', 'contribution_install');

function contribution_install()
{	
	define_metafield('Online Submission', 'Indicates whether or not this Item has been contributed from a front-end contribution form.');
	
	define_metafield('Posting Consent', 'Indicates whether or not the contributor of this Item has given permission to post this to the archive. (Yes/No)');
	
	define_metafield('Submission Consent', 'Indicates whether or not the contributor of this Item has given permission to submit this to the archive. (Yes/No)');
	
	
	//If the plugin version # is already stored in the DB, then we have created the contributors table before
	if($version = get_option('contribution_plugin_version')) {
		
		switch ($version) {
			//If we are on the first version of the plugin, do nothing
			case 1:			
			default:
				# code...
				break;
		}
	}
	//Otherwise we have to create the table from scratch
	else {		
		db_query("CREATE TABLE IF NOT EXISTS `contributors` (
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

 

?>
