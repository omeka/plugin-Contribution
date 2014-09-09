<?php if (!$type): ?>
<p><?php echo __('You must choose a contribution type to continue.'); ?></p>
<?php else: ?>
<h2><?php echo __('Contribute a %s', $type->display_name); ?></h2>

<?php
$allow_multiple_files = (boolean) $type->multiple_files;

if ($type->isFileRequired()):
    $required = true;
?>

<?php if ($allow_multiple_files) : ?>
<script type="text/javascript" charset="utf-8">
jQuery(window).load(function () {
    Omeka.Items.enableAddFiles(<?php echo js_escape(__('Add Another File')); ?>);
});
</script>
<div id="files-form" class="field drawer-contents">
    <?php echo $this->formLabel('contributed_file', __('Upload a file'), array(
        'id' => 'file-inputs',
    )); ?>
    <div id="files-metadata" class="field">
        <div id="upload-files" class="files"><?php /* four columns omega */ ?>
            <?php echo $this->formFile('contributed_file[0]', array('class' => 'fileinput button')); ?>
            <p><?php echo __('The maximum files size is %s.', max_file_size()); ?></p>
        </div>
    </div>
</div>
<?php else: ?>
<div class="field">
    <?php echo $this->formLabel('contributed_file', __('Upload a file')); ?>
    <?php echo $this->formFile('contributed_file', array('class' => 'fileinput button')); ?>
    <p><?php echo __('The maximum file size is %s.', max_file_size()); ?></p>
</div>
<?php endif; ?>

<?php endif; ?>

<?php
foreach ($type->getTypeElements() as $contributionTypeElement) {
    echo $this->elementForm($contributionTypeElement->Element, $item, array('contributionTypeElement' => $contributionTypeElement));
}
?>

<?php if ($type->add_tags) : ?>
<div id="tag-form" class="field">
    <div>
        <?php echo $this->formLabel('tags', __('Add Tags')); ?>
    </div>
    <div>
        <p id="add-tags-explanation" class="explanation"><?php echo __('Separate tags with %s', option('tag_delimiter')); ?></p>
        <?php echo $this->formText('tags'); ?>
    </div>
</div>
<?php endif; ?>

<?php
if (!isset($required) && $type->isFileAllowed()):
?>
<?php if ($allow_multiple_files) : ?>
<script type="text/javascript" charset="utf-8">
jQuery(window).load(function () {
    Omeka.Items.enableAddFiles(<?php echo js_escape(__('Add Another File')); ?>);
});
</script>
<div id="files-form" class="field drawer-contents">
    <?php echo $this->formLabel('contributed_file', __('Upload a file (Optional)'), array(
        'id' => 'file-inputs',
    )); ?>
    <div id="files-metadata" class="field">
        <div id="upload-files" class="files"><?php /* four columns omega */ ?>
            <?php echo $this->formFile('contributed_file[0]', array('class' => 'fileinput button')); ?>
            <p><?php echo __('The maximum files size is %s.', max_file_size()); ?></p>
        </div>
    </div>
</div>
<?php else: ?>
<div class="field">
    <?php echo $this->formLabel('contributed_file', __('Upload a file (Optional)')); ?>
    <?php echo $this->formFile('contributed_file', array('class' => 'fileinput button')); ?>
    <p><?php echo __('The maximum file size is %s.', max_file_size()); ?></p>
</div>
<?php endif; ?>

<?php endif; ?>

<?php $user = current_user(); ?>
<?php if (get_option('contribution_simple') && !$user) : ?>
<div class="field">
    <?php echo $this->formLabel('contribution_simple_email', __('Email (Required)')); ?>
    <?php
        if (isset($_POST['contribution_simple_email'])) {
            $email = $_POST['contribution_simple_email'];
        } else {
            $email = '';
        }
    ?>
    <?php echo $this->formText('contribution_simple_email', $email); ?>
</div>

<?php else: ?>
    <p><?php echo __('You are logged in as: %s', metadata($user, 'name')); ?>
<?php endif; ?>
    <?php
    //pull in the user profile form if it is set
    if (isset($profileType)): ?>

    <script type="text/javascript" charset="utf-8">
    //<![CDATA[
    jQuery(document).bind('omeka:elementformload', function (event) {
         Omeka.Elements.makeElementControls(event.target, <?php echo js_escape(url('user-profiles/profiles/element-form')); ?>,'UserProfilesProfile'<?php if ($id = metadata($profile, 'id')) echo ', ' . $id; ?>);
         Omeka.Elements.enableWysiwyg(event.target);
    });
    //]]>
    </script>

        <h2 class='contribution-userprofile <?php echo $profile->exists() ? "exists" : ""  ?>'><?php echo  __('Your %s profile', $profileType->label); ?></h2>
        <p id='contribution-userprofile-visibility'>
        <?php if ($profile->exists()) :?>
            <span class='contribution-userprofile-visibility'><?php echo __('Show'); ?></span><span class='contribution-userprofile-visibility' style='display:none'><?php echo __('Hide'); ?></span>
            <?php else: ?>
            <span class='contribution-userprofile-visibility' style='display:none'><?php echo __('Show'); ?></span><span class='contribution-userprofile-visibility'><?php echo __('Hide'); ?></span>
        <?php endif; ?>
        </p>
        <div class='contribution-userprofile <?php echo $profile->exists() ? "exists" : ""  ?>'>
        <p class="user-profiles-profile-description"><?php echo $profileType->description; ?></p>
        <fieldset name="user-profiles">
        <?php
        foreach($profileType->Elements as $element) {
            echo $this->profileElementForm($element, $profile);
        }
        ?>
        </fieldset>
        </div>
        <?php endif; ?>

<?php
// Allow other plugins to append to the form (pass the type to allow decisions
// on a type-by-type basis).
fire_plugin_hook('contribution_type_form', array('type' => $type, 'view' => $this));
?>
<?php endif; ?>
