<?php if (!$type): ?>
<p>You must choose a contribution type to continue.</p>
<?php else: ?>
<?php if ($type->file_allowed && $type->file_required): ?>
<div class="field">
	<input type="hidden" name="MAX_FILE_SIZE" value="30000000" />
	<label for="file">Upload a file</label>
	<input type="file" class="textinput" name="contributed_file" id="file-upload" class="fileinput" />	
</div>
<?php endif; ?>
<?php foreach ($type->getTypeElements() as $element) {
    echo $this->elementForm($element, $item);
} ?>
<?php if ($type->file_allowed && !$type->file_required): ?>
<div class="field">
	<input type="hidden" name="MAX_FILE_SIZE" value="30000000" />
	<label for="file">Upload a file <br /> (Optional)</label>
	<input type="file" class="textinput" name="contributed_file" id="file-upload" class="fileinput" />	
</div>
<?php endif; ?>
<?php endif; ?>