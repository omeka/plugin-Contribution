<?php head(array('title'=>'Make Your Contribution')); ?>

<script type="text/javascript" charset="utf-8">
//<![CDATA[

	Event.observe(window,'load', function() {
		$('contribution_type').onchange = function() {
			var type = $F(this);			
			var uri = "<?php echo uri(array('action'=>'partial'), 'contributionLinks'); ?>";
			
			var textOfPartial = $('description') ? $F('description') : $F('text');
			
			new Ajax.Updater('contribution', uri, {
			    parameters: {
			        contributiontype: type,
			        text: textOfPartial
			    },
				onComplete: function(t) {
					new Effect.Highlight('contribution');
				}
			});
		}
		
		$('pick_a_type').remove();
	});

//]]>	
</script>

<div id="primary">

<?php echo flash(); ?>

	<h2>Contribute a Story or File</h2>

<form method="post" enctype="multipart/form-data" accept-charset="utf-8">

		
			
		<div class="field">
			<label>Title of Contribution (optional)</label>
			<input type="text" name="title" class="textinput" value="<?php echo h($_POST['title']); ?>" />
		</div>
	

		<div class="field">
			<label>What kind of contribution will you be making?</label>
			<?php 
				echo select(array('name'=>'type', 'id'=>'contribution_type'), 
					array('Document'=>'Story','Still Image'=>'Image','Moving Image'=>'Movie', 'Sound'=>'Audio'), empty($_POST['type']) ? 'Document' : h($_POST['type'])); 
			?>
			
			<input type="submit" name="pick_type" id="pick_a_type" value="Pick One" />
		</div>
	
		<div id="contribution">
		<?php 
			echo $this->action('partial', 'index', 'contribution', array(
			    'contributiontype'=>$_POST['type'], 
			    'description'=>$_POST['description'], 
			    'text'=>$_POST['text']));
		?>
		</div>	

		<div class="field">
			<p>Did you create this?</p>
			<div class="radioinputs"><?php echo radio(array('name'=>'contributor_is_creator'), array('1'=>'Yes', '0'=>'No'), !empty($_POST['contributor_is_creator']) ? h($_POST['contributor_is_creator']) : 0); ?></div>
			<label>If not, please provide the name of the creator</label>
			<input type="text" class="textinput" name="creator" value="<?php echo h($_POST['creator']); ?>" />
		</div>	
		
		<div class="field">
			<label>In addition to saving your contribution to the archive, may we post it on this site?</label>
			<?php echo select(array('name'=>'posting_consent'), 
				array('Yes'=>'Yes, including my name', 'Anonymously'=>'Yes, but don\'t use my name', 'No'=>'No, only researchers should see it.'), !empty($_POST['posting_consent']) ? h($_POST['posting_consent']) : 'Yes'); 
			?>
		</div>
	
		<div class="field">
			<label>First Name:</label>
			<input type="text" name="contributor[first_name]" class="textinput" value="<?php echo h($_POST['contributor']['first_name']); ?>" />
		</div>
		
		<div class="field"><label>Last Name:</label>
		<input type="text" name="contributor[last_name]" class="textinput" value="<?php echo h($_POST['contributor']['last_name']); ?>" /></div>
		<div class="field"><label>Email:</label>
		<input type="text" class="textinput" name="contributor[email]" value="<?php echo h($_POST['contributor']['email']); ?>" />
		</div>


		<div class="field">
			<label>Keywords (separated by comma):</label>
			<input type="text" class="textinput" name="tags" value="<?php echo h($_POST['tags']) ?>" id="tags" />
		</div>

		<fieldset>
			<legend>Optional Information</legend>
			
			<div class="field">
			<label>Gender:</label>
			<input type="text" name="contributor[gender]" value="<?php echo h($_POST['contributor']['gender']); ?>" id="gender" />
			</div>

			<div class="field">
			<label>Race:</label>
			<input type="text" name="contributor[race]" value="<?php echo h($_POST['contributor']['race']); ?>" id="race" />
			</div>

			<div class="field">
			<label>Occupation:</label>
			<input type="text" name="contributor[occupation]" value="<?php echo h($_POST['contributor']['occupation']); ?>" id="occupation" />
			</div>

			<div class="field">
			<label>Birth Year:</label>
			<input type="text" name="contributor[birth_year]" value="<?php echo h($_POST['contributor']['birth_year']); ?>" id="birth_year" />
			</div>

			<div class="field">
			<label>Zipcode:</label>
			<input type="text" name="contributor[zipcode]" value="<?php echo h($_POST['contributor']['zipcode']); ?>" id="zipcode" />
			</div>
						
		</fieldset>
	
	    <fieldset>
	        <?php echo $captchaScript; ?>
	        <input type="submit" class="submitinput" name="submit" value="Submit" />
	    </fieldset>
</form>
</div>
<?php foot(); ?>