<?php 
/**
 * Notes on correct usage:
 * This plugin will not work correctly if one or more of the following item Types has been removed:
 *		Document
 *		Still Image
 *		Moving Image
 * 		Sound
 *
 * @author CHNM
 * @version $Id$
 * @copyright CHNM, 2007-2009
 * @package Contribution
 **/

/**
 * Plugin version.
 */
define('CONTRIBUTION_PLUGIN_VERSION', get_plugin_ini('Contribution', 'version'));

/**
 * Migration #.  Useful for upgrading the plugin.
 */
define('CONTRIBUTION_MIGRATION', 2);

/**
 * The default relative URL for the public-facing contribution form.  Can be
 * changed through the config form.
 */
define('CONTRIBUTION_PAGE_PATH', 'contribution/');

/**
 * Number of contributors to display per page on the admin form.
 * FIXME: This should also be set in the controller, though leaving it here 
 * makes it easier for plugin hackers to edit.
 */
define('CONTRIBUTORS_PER_PAGE', 10);

add_plugin_hook('define_routes', 'contribution_routes');
add_plugin_hook('config_form', 'contribution_config_form');
add_plugin_hook('config', 'contribution_config');
add_plugin_hook('install', array('Contribution_Upgrader', 'install'));
add_plugin_hook('define_acl', 'contribution_acl');
add_plugin_hook('item_browse_sql', 'contribution_view_items');
add_plugin_hook('after_validate_item', 'contribution_validate_item_contributor');
add_plugin_hook('before_insert_item', 'contribution_save_item_contributor');

add_filter('public_navigation_main', 'contribution_public_main_nav');
add_filter('admin_navigation_main', 'contribution_admin_nav');

add_filter(array('Form', 'Item', 'Contribution Form', 'Posting Consent'), 'contribution_posting_consent_form');
add_filter(array('Form', 'Item', 'Contribution Form', 'Submission Consent'), 'contribution_submission_consent_form');
add_filter(array('Form', 'Item', 'Contribution Form', 'Online Submission'), 'contribution_is_online_submission_form');
add_filter(array('Form', 'Item', 'Contribution Form', 'Contributor is Creator'), 'contribution_creator_is_contributor_form');

add_filter(array('Display', 'Item', 'Dublin Core', 'Contributor'), 'contribution_display_anonymous');
add_filter(array('Display', 'Item', 'Dublin Core', 'Creator'), 'contribution_display_anonymous');

/**
 * Add routes for the customized URL that points to the contribution form.
 * 
 * For example, if the stored path is like foobar/, the form will be reachable 
 * through http://your.omeka.site/foobar/.
 * 
 * @param Zend_Controller_Router_Rewrite $router
 * @return void
 **/
function contribution_routes($router)
{
	// get the base path
	$bp = get_option('contribution_page_path');
    
    if (!$bp) {
        return;
    }
    
    $router->addRoute('contributionAdd', new Zend_Controller_Router_Route($bp, array('module' => 'contribution', 'controller'=> 'index', 'action'=>'add')));
    
    // Secondary actions (thankyou, consent form, etc.) also need the URL.
	$router->addRoute('contributionLinks', new Zend_Controller_Router_Route($bp . ':action', array('module' => 'contribution', 'controller'=> 'index')));    
}

/**
 * HTML for the configuration form.
 * 
 * The following fields are configurable:
 *      contribution form URL
 *      contribution form email 'from' address
 *      consent text
 *      reCAPTCHA API keys
 * 
 * @return void
 **/
function contribution_config_form()
{
    // Deal with upgrading the plugin if necessary.
    $pluginVersion = (int)get_option('contribution_db_migration');
    // Skip the migrations if we don't need it.
    if ($pluginVersion < CONTRIBUTION_MIGRATION) {
        try {
            if (Contribution_Upgrader::upgrade($pluginVersion, CONTRIBUTION_MIGRATION)) {
	            echo '<div class="success">Contribution plugin was successfully upgraded!</div>';  
	        }
        } catch (Exception $e) {
            echo '<div class="error">An error occurred when attempting to ',
                 'upgrade your Contribution plugin: ', $e->getMessage() ,
                 '. Please notify your administrator and/or the plugin developers',
                 ' of this issue.<br /><br />',
                 '<pre>Error Trace: ', $e->getTraceAsString(), '</pre></div>';
        }
    }    
    
    
	$textInputSize = 30;
	$textAreaRows = 10;
	$textAreaCols = 50;
	?>
	
	<?php contribution_config_form_js(); ?>
	
	<div class="field">
	<label for="contribution_page_path">Relative Page Path From Project Root:</label>
	<div class="inputs">
	    <input type="text" name="contribution_page_path" value="<?php echo settings('contribution_page_path'); ?>" size="<?php echo $textInputSize; ?>" />
    	<p class="explanation">Please enter the relative page path from the project root where you want the contribution form to be located. Use forward slashes to indicate subdirectories, but do not begin with a forward slash.</p>
	</div>
	</div>
	
	<div class="field">
	<label for="contribution_contributor_email">Contributor 'From' Email Address:</label>
	<div class="inputs">
	    <input type="text" name="contribution_contributor_email" value="<?php echo settings('contribution_notification_email'); ?>" size="<?php echo $textInputSize; ?>" />
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
	<label for="contribution_collection_id">Default Collection For Contributed Items:</label>
	<div class="inputs">
	    <?php echo select_collection(array('name'=>'contribution_collection_id'), settings('contribution_collection_id'), null); ?>
	    <p class="explanation">(Optional) Please select the default collection for contributed items.<br/>Note: Changing the collection will not affect the collections of items that have already been contributed.</p>
	</div>
	</div>
	
	<div class="field">
	<label for="contribution_recaptcha_public_key">reCAPTCHA Public Key</label>
	<div class="inputs">
	    <input type="text" name="contribution_recaptcha_public_key" value="<?php echo settings('contribution_recaptcha_public_key') ?>" id="contribution_recaptcha_public_key" />
	    <p class="explanation">To enable CAPTCHA for the contribution form, please obtain a <a href="http://recaptcha.net/">ReCAPTCHA</a> API key and enter the relevant values.</p>
	</div>
	</div>
	
	<div class="field">
	<label for="contribution_recaptcha_private_key">reCAPTCHA Private Key</label>
	<div class="inputs">
	    <input type="text" name="contribution_recaptcha_private_key" value="<?php echo settings('contribution_recaptcha_private_key') ?>" id="contribution_recaptcha_private_key" />
	</div>
	</div>
<?php
}

/**
 * Include the Javascript for the Wysiwyg editor on the Contribution config form.
 * 
 * This is adapted directly from items/form.php on admin theme.
 * 
 * @return void
 **/
function contribution_config_form_js()
{
    echo js('tiny_mce/tiny_mce'); ?>
	<script type="text/javascript" charset="utf-8">
	    Event.observe(window, 'load', function(){
	        // Advanced config bombs out in IE6 for some reason. 
	        if (Prototype.Browser.IE) {
	            var config = {};
	        } else {
	            var config = {
	                theme: "advanced",
            		force_br_newlines : true,
            		forced_root_block : '', // Needed for 3.x
            		remove_linebreaks : true,
            		fix_content_duplication : false,
            		fix_list_elements : true,
            		valid_child_elements:"ul[li],ol[li]",
                   	theme_advanced_toolbar_location : "top",
                   	theme_advanced_buttons1 : "bold,italic,underline,justifyleft,justifycenter,justifyright,bullist,numlist,link,formatselect,code",
            		theme_advanced_buttons2 : "",
            		theme_advanced_buttons3 : "",
            		theme_advanced_toolbar_align : "left"
	            };
	        };
	        tinyMCE.init(config);
	        tinyMCE.execCommand("mceAddControl", false, 'contribution_consent_text');
	    });
	</script>
<?php    
}

/**
 * Saves the Contribution configuration form to the database.
 * 
 * Since the URL for contribution_page_path cannot be empty, it will save
 * the default URL if erased.
 * 
 * @see contribution_config_form()
 * @return void
 **/
function contribution_config()
{
    set_option('contribution_recaptcha_public_key', $_POST['contribution_recaptcha_public_key']);
    set_option('contribution_recaptcha_private_key', $_POST['contribution_recaptcha_private_key']);
	set_option('contribution_consent_text', $_POST['contribution_consent_text']);
	set_option('contribution_notification_email', $_POST['contribution_contributor_email']);
	set_option('contribution_page_path', $_POST['contribution_page_path']);
	set_option('contribution_collection_id', $_POST['contribution_collection_id']);
	
	//if the page path is empty then make it the default page path
	if (trim(get_option('contribution_page_path')) == '') {
		set_option('contribution_page_path', CONTRIBUTION_PAGE_PATH);
	}	
}

/**
 * Create a link to the contribution form.
 * 
 * @since 0.3 Returns string instead of echoing automatically.
 * @param string $text HTML text of the link.
 * @param array $attributes optional Attributes for the link.
 * @return string
 **/
function contribution_link_to_contribute($text, $attributes = array())
{
	return '<a href="' . uri(array(), 'contributionAdd') . '" ' . _tag_attributes($attributes) . ">$text</a>";
}

/**
 * Create the HTML for the consent form that must accompany all contributions
 * through the public form.
 * 
 * @return void
 **/
function contribution_embed_consent_form() 
{
?>
	<form action="<?php echo uri(array('action'=>'submit'), 'contributionLinks'); ?>" id="consent" method="post" accept-charset="utf-8">

			<h3>Please read this carefully:</h3>
			
			<div id="contribution_consent">
				<p><?php echo get_option('contribution_consent_text'); // Will be valid HTML. ?></p>
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

/**
 * Append a link to the list of contributors to the admin panel navigation.
 * 
 * @param array $navArray The array of navigation elements to filter.
 * @return array
 **/
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

/**
 * Append a link to the contribution form to the public navigation.
 * 
 * TODO: This may need to be configurable (text of the link as well as whether
 * or not to display it).
 * @param array $navArray Navigation elements to filter.
 * @return array
 **/
function contribution_public_main_nav($navArray) 
{
    $navArray['Contribute'] = uri(array(), 'contributionAdd');
    return $navArray;
}

/**
 * Adds ACL settings controlling access to 'browse', 'edit' and 'delete' actions
 * within the Contribution Index controller.
 * 
 * By default, admin and super users have access to these.
 * 
 * TODO: Edit and delete access is controlled, but there still needs to be an 
 * administrative interface for these actions.  
 * @param Omeka_Acl $acl
 * @return void
 **/
function contribution_acl($acl)
{
    $acl->loadResourceList(array('Contribution_Index'=>array('browse', 'edit', 'delete', 'batch')));
}

/**
 * Filter the SQL statement for browsing items so that users can view items that
 * are associated with a single Contributor.
 * 
 * @param Omeka_Db_Select $select
 * @param array $params
 * @return void
 **/
function contribution_view_items($select, $params)
{
    // Could come from $_GET or from $params.
    if (array_key_exists('contributor', $_GET)) {
        $contributorId = (int)$_GET['contributor'];
    } else if (array_key_exists('contributor', $params)) {
        $contributorId = (int)$params['contributor'];
    } else {
        return;
    }
            
    $db = get_db();
    
    // Join the following tables: items --> entities_relations 
    // --> entity_relationships --> entities --> contributors.
    $select->joinInner(array('con_er'=>$db->EntitiesRelations), 
        'con_er.relation_id = i.id AND con_er.type = "Item"', array())
    ->joinInner(array('con_e'=>$db->Entity), 
        'con_e.id = con_er.entity_id', array())
    ->joinInner(array('con'=>$db->Contributor), 
        'con.entity_id = con_e.id', array())
    ->joinInner(array('con_rel'=>$db->EntityRelationships), 
        'con_rel.id = con_er.relationship_id AND con_rel.name = "Added"', array())
        
    // And search based on the contributor that was provided.
    ->where('con.id = ?', $contributorId);
}

/**
 * Validate the Contributor prior to inserting the Item.  Ensure that any 
 * validation errors from the Contributor also appear on the form.
 * 
 * @param Item $item
 * @return void
 **/
function contribution_validate_item_contributor($item)
{
    // Check if we are actually contributing something via the public form.
    if (Zend_Registry::isRegistered('contributor')) {
        // Validate the Contributor only if it's not persistent in the database
        // yet.
        if (($contributor = Zend_Registry::get('contributor')) &&
            !$contributor->exists() &&
            !$contributor->isValid()) {
                // Validate and attach any error messages to the item.
                $item->addError('Contributor', $contributor->getErrors());    
        }
    }
}

/**
 * Save the Contributor record to the database (if necessary).  
 * 
 * Happens in the before_insert hook so that tags can be properly added AFTER
 * the item is inserted.
 * 
 * @see contribution_validate_item_contributor()
 * @param Item $item
 * @return void
 **/
function contribution_save_item_contributor($item)
{
    if (Zend_Registry::isRegistered('contributor')) {
        if (($contributor = Zend_Registry::get('contributor')) &&
            !$contributor->exists()) {
            // Will throw exceptions if necessary (if something went wrong).
            $contributor->forceSave();
        }
    }
}

/**
 * Filter the admin items form to create a select menu for the 'Posting Consent'
 * field.
 * 
 * Possible values: 'Not Applicable, 'Yes', 'No', 'Anonymously'. 
 * 
 * @param string $html Default HTML for the form input (ignored).
 * @param string $inputNameStem The form name for the input.  Note that [text]
 * must be appended for this to work.
 * @param string $consent The value of this field from the database.
 * @param array $options Any options passed to the ElementForm view helper 
 * (ignored).
 * @param Item $item The item that is represented on the form (ignored).
 * @param Element $element The Element record corresponding to the element being
 * displayed (ignored).
 * @return string HTML
 **/
function contribution_posting_consent_form($html, $inputNameStem, $consent, $options, $item, $element)
{
    return __v()->formSelect($inputNameStem . '[text]', $consent, null, array(''=>'Not Applicable', 'Yes'=>'Yes', 'No'=>'No', 'Anonymously'=>'Anonymously'));
}

/**
 * Filter the admin items form to create a select menu for the 'Submission 
 * Consent' field.
 * 
 * Possible values: 'Not Applicable', 'Yes', 'No'
 * 
 * @see contribution_posting_consent_form()
 * @param string
 * @param string
 * @param string
 * @return string
 **/
function contribution_submission_consent_form($html, $inputNameStem, $consent)
{
    return __v()->formSelect($inputNameStem . '[text]', $consent, null, array(''=>'Not Applicable', 'No'=>'No', 'Yes'=>'Yes'));
}

/**
 * Filter the admin items form to create a select menu for the 'Creator is 
 * Contributor' field.
 * 
 * Possible values: 'Not Applicable', 'Yes', 'No'
 * 
 * @see contribution_posting_consent_form()
 * @param string
 * @param string
 * @param string
 * @return string
 **/
function contribution_creator_is_contributor_form($html, $inputNameStem, $isSame)
{
    return __v()->formSelect($inputNameStem . '[text]', $isSame, null, array(''=>'Not Applicable', 'No'=>'No', 'Yes'=>'Yes'));
}

/**
 * Filter the admin items form to create a select menu for the 'Online 
 * Submission' field.
 * 
 * Possible values: 'Yes', 'No'
 * 
 * @see contribution_posting_consent_form()
 * @param string
 * @param string
 * @param string
 * @return string
 **/
function contribution_is_online_submission_form($html, $inputNameStem, $consent)
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


