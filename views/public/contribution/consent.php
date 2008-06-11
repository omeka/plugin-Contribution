<?php head(); ?>

<style type="text/css" media="screen">
	/* Rights field is hidden by default */
	#rights {display:none;} 
</style>

<div id="primary">
	<h1>Contribute a Story or File</h1>
<form action="<?php echo uri('contribution/submit') ?>" id="consent" method="post" accept-charset="utf-8">

		<h3>Please read this carefully:</h3>
		
		<p>You must be 13 years of age or older to submit material to us. Your submission of material constitutes your permission for, and consent to, its dissemination and use in connection with <em><?php settings('site_title'); ?></em> in all media in perpetuity. If you have so indicated on the form, your material will be published on <em><?php settings('site_title'); ?></em> (with or without your name, depending on what you have indicated). Otherwise, your response will only be available to approved researchers using <em><?php settings('site_title'); ?></em>. The material you submit must have been created by you, wholly original, and shall not be copied from or based, in whole or in part, upon any other photographic, literary, or other material, except to the extent that such material is in the public domain. Further, submitted material must not violate any confidentiality, privacy, security or other laws.</p>

		<p>By submitting material to <em><?php settings('site_title'); ?></em> you release, discharge, and agree to hold harmless <em><?php settings('site_title'); ?></em> and persons acting under its permission or authority, including a public library or archive to which the collection might be donated for purposes of long-term preservation, from any claims or liability arising out the <em><?php settings('site_title'); ?></em>'s use of the material, including, without limitation, claims for violation of privacy, defamation, or misrepresentation.</p>

		<p><em><?php settings('site_title'); ?></em> has no obligation to use your material.</p>

		<p>You will be sent via email a copy of your contribution to <em><?php settings('site_title'); ?></em>. We cannot return any material you submit to us so be sure to keep a copy. <em><?php settings('site_title'); ?></em> will not share your email address or any other information with commercial vendors.</p>
		<div class="field">
			
			<!--The text of this field with be stored in the database as the Dublin Core value for 'Rights'-->
			<textarea name="rights" id="rights" rows="20" cols="80" readonly="true">You must be 13 years of age or older to submit material to us. Your submission of material constitutes your permission for, and consent to, its dissemination and use in connection with <?php settings('site_title'); ?> in all media in perpetuity. If you have so indicated on the form, your material will be published on <?php settings('site_title'); ?> (with or without your name, depending on what you have indicated). Otherwise, your response will only be available to approved researchers using <?php settings('site_title'); ?>. The material you submit must have been created by you, wholly original, and shall not be copied from or based, in whole or in part, upon any other photographic, literary, or other material, except to the extent that such material is in the public domain. Further, submitted material must not violate any confidentiality, privacy, security or other laws.

By submitting material to <?php settings('site_title'); ?> you release, discharge, and agree to hold harmless <?php settings('site_title'); ?> and persons acting under its permission or authority, including a public library or archive to which the collection might be donated for purposes of long-term preservation, from any claims or liability arising out the <?php settings('site_title'); ?>'s use of the material, including, without limitation, claims for violation of privacy, defamation, or misrepresentation.

<?php settings('site_title'); ?> has no obligation to use your material.

You will be sent via email a copy of your contribution to <?php settings('site_title'); ?>. We cannot return any material you submit to us so be sure to keep a copy. <?php settings('site_title'); ?> will not share your email address or any other information with commercial vendors.</textarea>
		</div>
		
		<div class="field">
			<p>Please give your consent below</p>
			<div class="radioinputs"><?php radio(array('name'=>'submission_consent'), 
					array(	'Yes'		=> 'I Agree. Please include my contribution.',
							'No'		=> 'No, I do not agree.'), 'Yes'); ?></div>
		</div>
	
	<input type="submit" class="submitinput" name="submit" value="Submit" />
</form>
</div>
<?php foot(); ?>