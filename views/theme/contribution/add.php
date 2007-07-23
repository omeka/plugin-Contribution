<?php head(); ?>

<script type="text/javascript" charset="utf-8">
	Event.observe(window,'load', function() {
		return false;
	});
</script>

	<h1>Make a Contribution</h1>

<form action="<?php echo uri('contribution/submit') ?>" method="post" accept-charset="utf-8">
	
	<div class="field">
		<label>Title (optional)</label>
		<input type="text" name="title" value="<?php echo $item->title; ?>" />
	</div>
	
	<div class="field">
		<label>Description (optional)</label>
		<input type="text" name="description" value="<?php echo $item->description; ?>" />
	</div>

	<ul id="menu">
	
	<?php 
		$nav = array('Story'=> uri('contribute', array('type'=>'story')), 'Image'=> uri('contribute', array('type'=>'image')));
		nav($nav); 
	?>
</ul>
	
	<div id="contribution">
	<?php 
		common($partial, array('data'=>$_POST), 'contribution'); 
	?>
	</div>
	
	
	<input type="submit" name="submit" value="Submit your Contribution --&gt;" />
</form>
	

<?php foot(); ?>