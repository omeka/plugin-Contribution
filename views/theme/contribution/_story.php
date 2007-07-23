<?php 
	if(!isset($data)) {
		$data = $_REQUEST;
	} 
?>

<div class="field">
	<label for="text">Please tell us your story</label>
	<textarea name="Metatext[Text]" id="text"><?php echo $data['Metatext']['Text']; ?></textarea>
</div>

<input type="hidden" name="type" value="Document" />