<?php 
	if(!isset($data)) {
		$data = $_REQUEST;
	} 
?>

<div class="field">
	<label for="text">Please tell us your story</label>
	<textarea name="text" id="text" rows="30" cols="80"><?php echo $data['text']; ?></textarea>
</div>
