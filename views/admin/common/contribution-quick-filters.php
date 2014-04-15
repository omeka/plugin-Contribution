<ul class="quick-filter-wrapper">
    <li><a href="#" tabindex="0"><?php echo __('Filter by status'); ?></a>
    <ul class="dropdown">
        <li><span class="quick-filter-heading"><?php echo __('Filter by status') ?></span></li>
        <li><a href="<?php echo url('contribution/items'); ?>"><?php echo __('View All') ?></a></li>
        <li><a href="<?php echo url('contribution/items', array('status' => 'public')); ?>"><?php echo __('Public'); ?></a></li>
        <li><a href="<?php echo url('contribution/items', array('status' => 'private')); ?>"><?php echo __('Private'); ?></a></li>
        <li><a href="<?php echo url('contribution/items', array('status' => 'review')); ?>"><?php echo __('Needs review'); ?></a></li>
    </ul>
    </li>
</ul>
