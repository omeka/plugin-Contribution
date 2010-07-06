<?php if (!$type): ?>
<p>You must choose a contribution type to continue.</p>
<?php 
else:
if ($type->file_allowed && $type->file_required):
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
if ($type->file_allowed && !$type->file_required):
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
endif;