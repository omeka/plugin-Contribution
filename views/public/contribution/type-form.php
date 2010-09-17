<?php if (!$type): ?>
<p>You must choose a contribution type to continue.</p>
<?php 
else:
if ($type->isFileRequired()):
    $required = true;
?>
<div class="field">
        <?php echo $this->formLabel('contributed_file', 'Upload a file'); ?>
        <?php echo $this->formFile('contributed_file', array('class' => 'fileinput')); ?>
</div>
<?php 
endif;
foreach ($type->getTypeElements() as $element) {
    echo $this->elementForm($element, $item);
}
if (!isset($required) && $type->isFileAllowed()):
?>
<div class="field">
        <?php echo $this->formLabel('contributed_file', 'Upload a file (Optional)'); ?>
        <?php echo $this->formFile('contributed_file', array('class' => 'fileinput')); ?>
</div>
<?php 
endif;
// Allow other plugins to append to the form (pass the type to allow decisions
// on a type-by-type basis).
fire_plugin_hook('contribution_append_to_type_form', $type);

?>
<div id="contributor-metadata">
<fieldset>
    <h3>Personal Information</h3>
<div class="field">
    <label>Name</label>
    <div class="inputs">
        <div class="input">
            <?php echo $this->formText('contributor-name', $_POST['contributor-name'], array('class' => 'textinput')); ?>
        </div>
    </div>
</div>
<div class="field">
    <label>Email Address:</label>
    <div class="inputs">
        <div class="input">
            <?php echo $this->formText('contributor-email', $_POST['contributor-email'], array('class' => 'textinput')); ?>
        </div>
    </div>
</div>
<?php
foreach (contribution_get_contributor_fields() as $field) {
    echo $field;
}
endif;
?>
</fieldset>
</div>