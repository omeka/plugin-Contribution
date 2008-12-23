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
define('CONTRIBUTION_PAGE_PATH', 'contribution/');

add_plugin_hook('define_routes', 'contribution_routes');
add_plugin_hook('config_form', 'contribution_config_form');
add_plugin_hook('config', 'contribution_config');
add_plugin_hook('before_update_item', 'contribution_save_info');
// temporarily commented out
//add_plugin_hook('append_to_item_show', 'contribution_show_info');
//add_plugin_hook('append_to_item_form', 'contribution_edit_info');
add_plugin_hook('install', 'contribution_install');

add_filter('public_navigation_main', 'contribution_public_main_nav');
add_filter('admin_navigation_main', 'contribution_admin_nav');

function contribution_routes($router)
{
	// get the base path
	$bp = get_option('contribution_page_path');

    $router->addRoute('contributionAdd', new Zend_Controller_Router_Route($bp, array('module' => 'contribution', 'controller'=> 'index', 'action'=>'add')));
    
	$router->addRoute('contributionLinks', new Zend_Controller_Router_Route($bp . ':action', array('module' => 'contribution', 'controller'=> 'index')));
    
    $router->addRoute('contributionFormPartial', new Zend_Controller_Router_Route($bp . 'partial/:contributiontype', array('module' => 'contribution', 'controller'=> 'index', 'action'=>'partial')));	
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
	set_option('contribution_page_path', contribution_clean_path(CONTRIBUTION_PAGE_PATH));
	set_option('contribution_require_tos_and_pp', FALSE);
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

function contribution_clean_path($path)
{
	return trim(trim($path), '/') . '/';
}

function contribution_config($post)
{
	set_option('contribution_consent_text', $post['contribution_consent_text']);
	set_option('contribution_notification_email', $post['contributor_email']);
	set_option('contribution_page_path',  contribution_clean_path($post['contribution_page_path']));
	set_option('contribution_require_tos_and_pp', strtolower($post['contribution_require_tos_and_pp']) == 'on');
	
	//if the page path is empty then make it the default page path
	if (trim(get_option('contribution_page_path')) == '') {
		set_option('contribution_page_path', contribution_clean_path(CONTRIBUTION_PAGE_PATH));
	}	
}

function contribution_partial()
{
	$partial = Zend_Registry::get('contribution_partial');
	common($partial, array('data'=>$_POST), 'contribution'); 
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
    return $navArray += array('Contributers'=> uri('contribution/contributors'));
}

function contribution_public_main_nav($navArray) {
    $navArray['Contribute'] = uri('contribution');
    return $navArray;
}