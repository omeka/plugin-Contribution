
<li class="element" id="<?php echo html_escape($element_id_name); ?>-container" aria-label="<?php echo __('New existing element'); ?>" role="group">
    <div class="flash success sr-only" role="alert">Element successfully added.</div>
    <div class="sortable-item drawer">
        <label class="existing-element-dropdown" id="<?php echo $element_id_name; ?>-label"><?php echo __('Element'); ?></label>
        <?php
        $elementsArray = get_table_options(
                'Element', null,
                    array(
                        'sort' => 'alpha',
                        'item_type_id' => $item_type_id,
                    )
                );
        echo $this->formSelect($element_id_name, $element_id_value, array('aria-labelledby' => $element_id_name . '-label', 'class' => 'drawer-name', 'required' => "true"), $elementsArray);
        ?>
        </label>
        <button type="button" aria-expanded="false" aria-label="<?php echo __('Undo %s removal', __('new element')); ?>" class="undo-delete" data-action-selector="deleted" title="<?php echo __('Undo %s removal', __('new element')); ?>"><span class="icon" aria-hidden="true"></span></button>
        <button type="button" aria-expanded="true" class="delete-drawer" data-action-selector="deleted" title="<?php echo __('Remove %s', __('new element')); ?>" aria-label="<?php echo __('Remove %s', __('new element')); ?>"><span class="icon" aria-hidden="true"></button>
    </div>
    <div class="drawer-contents opened">
        <div class="field">
            <div class="field-meta"><label class="prompt" id="<?php echo $element_prompt_name; ?>-label"><?php echo __("Prompt"); ?></label></div>
            <div class="inputs"><?php echo $this->formText($element_prompt_name, $element_prompt_value, array('aria-labelledby' => $element_prompt_name . '-label', 'required' => "true")); ?></div>
        </div>
        <div class="field">
            <div class="field-meta"><label class="long-text" id="<?php echo $element_long_name; ?>-label"><?php echo __('Multiple rows'); ?></label></div>
            <div class="inputs"><?php echo $this->formCheckbox($element_long_name, null, array('aria-labelledby' => $element_long_name . '-label')); ?></div>
            <?php echo $this->formHidden($element_order_name, $element_order_value, array('class' => 'element-order')); ?>
        </div>
    </div>
</li>
