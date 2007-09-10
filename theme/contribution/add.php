<?php head(); ?>
<style type="text/css" media="screen">
	#contribution {
		overflow:hidden;
	}
</style>
<script type="text/javascript" charset="utf-8">
//<![CDATA[

	Event.observe(window,'load', function() {
		$('contribution_type').onchange = function() {
			var type = $F(this);
			
			//This logic is duplicated in the contribution controller (any way to abstract it out?)
			switch(type) {
				case 'Still Image':
				case 'Moving Image':
				case 'Sound':
					var partial = "_file";
					break;
				case 'Document':
					var partial = "_document";
					break;
				default:
					break;
			}
			
			var uri = "<?php echo uri('contribution/'); ?>" + partial;
			
			new Ajax.Updater('contribution', uri, {
				onComplete: function(t) {
					new Effect.Highlight('contribution');
				}
			});
		}
		
		$('pick_a_type').remove();
	});

//]]>	
</script>
<?php 
	echo flash(); 
?>

	<h1>Make a Contribution</h1>

<form method="post" enctype="multipart/form-data" accept-charset="utf-8">

	<fieldset>
		<legend>Main Info</legend>

		<div class="field">
			<label>Title (optional)</label>
			<input type="text" name="title" value="<?php echo $_POST['title']; ?>" />
		</div>
	
		<div class="field">
			<label>Description (optional)</label>
			<input type="text" name="description" value="<?php echo $_POST['description']; ?>" />
		</div>
	
		<div class="field">
			<label>What kind of contribution will you be making?</label>
			<?php 
				select(array('name'=>'type', 'id'=>'contribution_type'), 
					array('Document'=>'Story','Still Image'=>'Image','Moving Image'=>'Movie', 'Sound'=>'Audio'), $_POST['type']); 
			?>
			
			<input type="submit" name="pick_type" id="pick_a_type" value="Pick One" />
		</div>
	
		<div id="contribution">
		<?php 
			contribution_partial();
		?>
		</div>	
	
		<div class="field">
			<label>Did you create this?</label>
			<?php radio(array('name'=>'contributor_is_creator'), array('1'=>'Yes', '0'=>'No'), !empty($_POST['contributor_is_creator']) ? $_POST['contributor_is_creator'] : 0); ?>
			<label>If not, please provide the name of the creator</label>
			<input type="text" name="creator" value="<?php echo $_POST['creator']; ?>" />
		</div>	
		
		<div class="field">
			<label>In addition to saving your contribution to the archive, may we post it on this site?</label>
			<?php select(array('name'=>'posting_consent'), 
				array('Yes'=>'Yes, including my name', 'Anonymously'=>'Yes, but don\'t use my name', 'No'=>'No, only researchers should see it.'), !empty($_POST['posting_consent']) ? $_POST['posting_consent'] : 'Yes'); 
			?>
		</div>
		
		
	</fieldset>
	
	
	<fieldset>
		<legend>Keywords</legend>

		<div class="field">
			<label>Enter keywords separated by commas:</label>
			<input type="text" name="tags" value="<?php echo $_POST['tags'] ?>" id="tags" />
		</div>
		
		
	</fieldset>

	
	<fieldset>
		<legend>Your Info</legend>
		<div class="field">
			<label>First Name:</label>
			<input type="text" name="contributor[first_name]" value="<?php echo $_POST['contributor']['first_name']; ?>" />
		</div>
		
		<div class="field"><label>Last Name:</label>
		<input type="text" name="contributor[last_name]" value="<?php echo $_POST['contributor']['last_name']; ?>" /></div>
		
		<div class="field"><label>Email:</label>
		<input type="text" name="contributor[email]" value="<?php echo $_POST['contributor']['email']; ?>" /></div>
		
		<div class="field"><label>Zipcode:</label>
		<input type="text" name="contributor[zipcode]" value="<?php echo $_POST['contributor']['zipcode']; ?>" size="5" /></div>
		</div>
		
		<div class="field">
			<label>Birth Year</label>
			<input type="text" name="contributor[birth_year]" value="<?php echo $_POST['contributor']['birth_year']; ?>" id="birth_year" />
		</div>
		
		<div class="field">
			<label>Race:</label>
			<input type="text" name="contributor[race]" value="<?php echo $_POST['contributor']['race']; ?>" id="race" />
		</div>
		
		<div class="field">
			<label>Gender:</label>
			<input type="text" name="contributor[gender]" value="<?php echo $_POST['contributor']['gender']; ?>" id="gender" />
		</div>
		
		<div class="field">
			<label>Occupation</label>
			<input type="text" name="contributor[occupation]" value="<?php echo $_POST['contributor']['occupation']; ?>" id="occupation" />
		</div>
	</fieldset>

	
	

	<input type="submit" name="submit" value="Submit your Contribution --&gt;" />
</form>
	

<?php foot(); ?>