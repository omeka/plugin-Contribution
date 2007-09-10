<?php head(); ?>

<form action="<?php echo uri('contribution/submit') ?>" method="post" accept-charset="utf-8">
	<fieldset>
		<legend>Consent</legend>
		
		<div class="field">
			<label>Please read this carefully:</label>
			<textarea name="rights" rows="30" cols="80" readonly="true">
 You are being asked to contribute your recollections to the Hurricane 
 Digital Memory Bank, which is developing a permanent digital record of 
 the events surrounding the major hurricanes of 2005. Your participation 
 in this project will allow future historians, and people such as yourself, 
 to gain a greater understanding of these events and the responses to them.

 You must be 13 years of age or older to submit material to us. Your  
 submission of material constitutes your permission for, and consent  
 to, its dissemination and use in connection with the Memory Bank in  
 all media in perpetuity. If you have so indicated on the form, your  
 material will be published on the Memory Bank website (with or  
 without your name, depending on what you have indicated). Otherwise,  
 your response will only be available to approved researchers using  
 the Memory Bank. The material you submit must have been created by  
 you, wholly original, and shall not be copied from or based, in whole  
 or in part, upon any other photographic, literary, or other material,  
 except to the extent that such material is in the public domain. Further, 
 submitted material must not violate any confidentiality, privacy, security 
 or other laws."

 By submitting material to the Memory Bank you release, discharge, and  
 agree to hold harmless the Memory Bank and persons acting under its  
 permission or authority, including a public library or archive to  
 which the collection might be donated for purposes of long-term  
 preservation, from any claims or liability arising out the Memory  
 Bank's use of the material, including, without limitation, claims for  
 violation of privacy, defamation, or misrepresentation.

 The Memory Bank has no obligation to use your material.

 You will be sent via email a copy of your contribution to the Memory  
 Bank. We cannot return any material you submit to us so be sure to  
 keep a copy. The Memory Bank will not share your email address or any  
 other information with commercial vendors.</textarea>
		</div>
		
		<div class="field">
			<label>Please give your consent below</label>
			<?php radio(array('name'=>'submission_consent'), 
					array(	'Yes'		=> 'I Agree. Please include my contribution.',
							'No'		=> 'I do not agree.',
							'Yes'		=> 'Yes' ), 'Yes'); ?>
		</div>
	</fieldset>
	
	<input type="submit" name="submit" value="Submit!" />
</form>

<?php foot(); ?>