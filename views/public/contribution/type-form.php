
<?php if (!$type): ?>
<p>You must choose a contribution type to continue.</p>
<?php else: ?>
<h2>Contribute a <?php echo $type->display_name; ?></h2>

<?php 
if ($type->isFileRequired()):
    $required = true;
?>



<div class="field">
        <?php echo $this->formLabel('contributed_file', 'Upload a file'); ?>
        <?php echo $this->formFile('contributed_file', array('class' => 'fileinput')); ?>
</div>

<?php endif; ?>

<?php 
foreach ($type->getTypeElements() as $contributionTypeElement) {
    echo $this->elementForm($contributionTypeElement->Element, $item, array('contributionTypeElement'=>$contributionTypeElement));
}
?>

<?php 
if (!isset($required) && $type->isFileAllowed()):
?>
<div class="field">
        <?php echo $this->formLabel('contributed_file', 'Upload a file (Optional)'); ?>
        <?php echo $this->formFile('contributed_file', array('class' => 'fileinput')); ?>
</div>
<?php endif; ?>

<?php 
//pull in the user profile form it is is set
$profileTypeId = get_option('contribution_user_profile_type');
$profileTypeId = 1;
if($profileTypeId): ?>

<?php 
$this->addHelperPath(USER_PROFILES_DIR . '/helpers', 'UserProfiles_View_Helper_');
$db = get_db();
$profile = $db->getTable('UserProfilesProfile')->findByUserIdAndTypeId(current_user()->id, $profileTypeId);
if(!$profile) {
    $profile = new UserProfilesProfile();
}
$profileType = $db->getTable('UserProfilesType')->find($profileTypeId);
?>

<script type="text/javascript" charset="utf-8">
//<![CDATA[
jQuery(document).bind('omeka:elementformload', function (event) {
     Omeka.Elements.makeElementControls(event.target, <?php echo js_escape(url('user-profiles/profiles/element-form')); ?>,'UserProfilesProfile'<?php if ($id = metadata($profile, 'id')) echo ', '.$id; ?>);
     Omeka.Elements.enableWysiwyg(event.target);
});
//]]>
</script>

    <h2><?php echo  __('Edit your %s profile', $profileType->label); ?></h2>
    <p class="user-profiles-profile-description"><?php echo $profileType->description; ?></p>
    <?php 
    foreach($profileType->Elements as $element) {
        echo $this->profileElementForm($element, $profile);
    }
    ?>
<?php endif; ?>

<?php 
// Allow other plugins to append to the form (pass the type to allow decisions
// on a type-by-type basis).
fire_plugin_hook('contribution_type_form', array('type'=>$type, 'view'=>$this));
?>
<?php endif; ?>
