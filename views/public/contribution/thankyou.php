<?php echo head(); ?>
<div id="primary">
	<h1>Thank you for contributing!</h1>
	<p>Your contribution will show up in the archive once an administrator approves it. Meanwhile, feel free to <?php echo contribution_link_to_contribute('make another contribution'); ?> or <a href="<?php echo url('items/browse'); ?>">browse the archive</a>.</p>
</div>
<?php echo foot(); ?>
