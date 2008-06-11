<div class="field">
	<input type="hidden" name="MAX_FILE_SIZE" value="30000000" />
	<label for="file">File a file</label>
	<input type="file" class="textinput" name="file[0]" id="file-upload" class="fileinput" />	
</div>	
<div class="field">
	<label>Description (optional)</label>
	<input type="text" name="description" class="textinput" id="description" value="<?php echo $_POST['description']; ?>" />
</div>