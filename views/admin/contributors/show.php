<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */
$name = html_escape($contributor->name);
queue_css_file('contributors');
contribution_admin_header(array(__('Contributors'), "$name"));
?>


<?php 
echo $this->partial('contribution-navigation.php');
?>

<div id="primary">
    <?php echo flash(); ?>
    <h2><?php echo $contributor->name; ?><?php echo __("'s contributions"); ?></h2>
    
    <?php if(plugin_is_active('UserProfiles')): ?>
    <div id='contribution-profile-info'>
        <?php 
            $this->addHelperPath(USER_PROFILES_DIR . '/helpers', 'UserProfiles_View_Helper_');
            echo $this->linkToOwnerProfile(array('owner'=>$contributor, 'text'=>__("Profile: ")));   
        ?>
    </div>
    <?php endif; ?>
    
    <div id='contribution-user-contributions'>
        <?php foreach($items as $item): ?>
        <?php set_current_record('item', $item->Item); ?>
        <section class="seven columns omega contribution">
            <?php 
                if ($item->Item->public) {
                    $status = __('Public');
                } else {
                    if($item->public) {
                        $status = __('Needs review');
                    } else {
                        $status = __('Private contribution');
                    }
                }
            ?>
        
            <h2><?php echo link_to_item(null, array(), 'edit'); ?></h2>
            <p><?php echo $status;?> <?php echo (boolean) $item->anonymous ? " | " . __('Anonymous') : "";  ?></p>
            <?php
            echo item_image_gallery(
                array('linkWrapper' => array('class' => 'admin-thumb panel')),
                'square_thumbnail', true);
            ?>
            <?php echo all_element_texts('item'); ?>
        </section>   
        
        <?php endforeach; ?>

    </div>
</div>
<?php echo foot(); ?>
