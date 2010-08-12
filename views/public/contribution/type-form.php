<?php if (!$type): ?>
<p>You must choose a contribution type to continue.</p>
<?php 
else:
if ($type->isFileRequired()):
    $required = true;
?>
<div class="field">
	<input type="hidden" name="MAX_FILE_SIZE" value="30000000" />
	<label for="file">Upload a file</label>
	<input type="file" class="textinput" name="contributed_file" id="file-upload" class="fileinput" />	
</div>
<?php 
endif;
foreach ($type->getTypeElements() as $element) {
    echo $this->elementForm($element, $item);
}
if (!isset($required) && $type->isFileAllowed()):
?>
<div class="field">
	<input type="hidden" name="MAX_FILE_SIZE" value="30000000" />
	<label for="file">Upload a file <br /> (Optional)</label>
	<input type="file" class="textinput" name="contributed_file" id="file-upload" class="fileinput" />	
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
            <input type="text" class="textinput" name="contributor_name" id="contributor-name" />
        </div>
    </div>
</div>
<div class="field">
    <label>Email Address:</label>
    <div class="inputs">
        <div class="input">
            <input type="text" class="textinput" name="contributor_email" id="contributor-email" />
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