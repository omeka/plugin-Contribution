<?php if(submitted_through_contribution_form($item)): ?>
	<h3>Permission to post contributor's submission to the archive:</h3>
	<?php select(array('name'=>'submission_consent', 'id'=>'submission_consent'), array('Yes'=>'Yes', 'No'=>'No'), $item->getMetatext('Submission Consent')); ?>

	<h3>Permission to post the contributor's submission to the public site:</h3>
	<?php 
	select(array('name'=>'posting_consent', 'id'=>'posting_consent'), array('Yes'=>'Yes','No'=>'No','Anonymously'=>'Anonymously'), $item->getMetatext('Posting Consent')); ?>

<?php	
endif;
?>
