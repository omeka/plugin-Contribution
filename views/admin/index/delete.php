<?php head(); ?>

<div id="primary">
    <h2>Delete the following contributors from the system?</h2>
    <ul>
        <?php foreach ($contributors as $contributor): ?>
            <li><?php echo html_escape($contributor->name); ?> (<?php echo html_escape($contributor->email); ?>)</li>
        <?php endforeach; ?>
    </ul>
    
    <form action="<?php echo uri(array('action'=>'delete')); ?>" method="post" accept-charset="utf-8">
        <?php echo $this->formHidden('contributor_id', implode(',', pluck('id', $contributors))); ?>
        <?php echo $this->formHidden('do_delete', 1); ?>
        
        <?php echo $this->formLabel('delete_items', 'Delete All Items associated
        with these Contributors'); ?>
        <?php echo $this->formCheckbox('delete_items');?>
        <?php echo $this->formSubmit('delete_contributors', 'Continue →'); ?>
        <?php echo $this->formSubmit('cancel_delete', 'Cancel'); ?>
    </form>
</div>
<?php foot(); ?>