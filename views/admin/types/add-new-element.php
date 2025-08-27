<li class="element">
    <div class="sortable-item">
        <?php
        echo $this->formText(
            $element_name_name, $element_name_value,
            array('placeholder' => __('Element Name'))
        );
        ?>
        <?php
        echo $this->formHidden(
            $element_order_name, $element_order_value,
            array('class' => 'element-order')
        );
        ?>
        <button type="button" class="delete-drawer" ata-action-selector="deleted" title="<?php echo __('Remove'); ?>" aria-label="<?php echo __('Remove'); ?>"><span class="icon" aria-hidden="true"></button>
    </div>
    <div class="drawer-contents">
        <?php
        echo $this->formTextarea(
            $element_description_name, $element_description_value,
            array(
                'placeholder' => __('Element Description'),
                'rows' => '3',
                'cols'=>'30'
            )
        );
        ?>
    </div>
</li>
