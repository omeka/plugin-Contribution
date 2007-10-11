<style type="text/css" media="screen">
	#contribution_plugin {
		margin-bottom: 1.5em;
		border-bottom: 1px solid #CCCCCC;
	}
</style>

<div id="contribution_plugin">

<h2>Contribution Status*</h2>

<?php 
$is_submission = (bool) item_metadata($item, 'Online Submission');

if(!$is_submission): ?>

	<h3>This item was not contributed through the public contribution form.</h3>

<?php else: ?>

	<div class="field">
	<h3>Submission Consent:</h3>
	<div>
	<?php display_empty(item_metadata($item, 'Submission Consent')); ?>
	</div>
	</div>
	
	<div class="field">
	<h3>Posting Consent:</h3>
	<div>
	<?php display_empty(item_metadata($item, 'Posting Consent')); ?>
	</div>
	</div>

<em>*Note: This item can still be made viewable by the general public regardless of the whether the contributor has given permission.</em>

<?php endif; ?>

</div>