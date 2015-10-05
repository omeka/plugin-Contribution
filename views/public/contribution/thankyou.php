<?php echo head(); ?>
<div id="primary">
	<h1><?php echo __("Thank you for contributing!"); ?></h1>
	<p><?php echo __("Your contribution will show up in the archive once an administrator approves it. Meanwhile, feel free to <a href='" . contribution_contribute_url() . "'>%s</a> or <a href='" . url('items/browse') . "'>%s</a>." , __('make another contribution'), __('browse the archive') ); ?>
	</p>
	<?php if(get_option('contribution_simple') && !current_user()): ?>
	<p><?php echo __("If you would like to interact with the site further, you can use an account that is ready for you. Visit <a href='" . url('users/forgot-password') . "'>%s</a>, and request a new password for the email you used", __('this page')); ?>
	<?php endif; ?>
</div>
<?php echo foot(); ?>
