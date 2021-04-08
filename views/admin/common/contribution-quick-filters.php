<select class="quick-filter">
    <option class="quick-filter-heading"><?php echo __('Filter by status') ?></option>
    <option value="<?php echo url('contribution/items', array('status' => 'public')); ?>"><?php echo __('Public'); ?></option>
    <option value="<?php echo url('contribution/items', array('status' => 'private')); ?>"><?php echo __('Private'); ?></option>
    <option value="<?php echo url('contribution/items', array('status' => 'review')); ?>"><?php echo __('Needs review'); ?></option>
</select>