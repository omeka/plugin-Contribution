<div class="field">
	<input type="hidden" name="MAX_FILE_SIZE" value="30000000" />
	<label for="file">Upload a file</label>
	<input type="file" class="textinput" name="file[0]" id="file-upload" class="fileinput" />	
</div>	
<div class="field">
	<label>Description (optional)</label>
	<?php echo $this->formText('description', $this->text, array('class'=>'textinput')); ?>
</div>