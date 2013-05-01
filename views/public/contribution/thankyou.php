<?php echo head(); ?>
<div id="primary">
	<h1><?php echo __("Thank you for contributing!"); ?></h1>
	<p><?php echo __("Your contribution will show up in the archive once an administrator approves it. Meanwhile, feel free to %s <?php echo ; ?> or %s ." , contribution_link_to_contribute('make another contribution'), "<a href='" . url('items/browse') . "'>browse the archive</a>"); ?>
	</p>
</div>
<?php echo foot(); ?>
