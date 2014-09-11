<li class="element">
    <div class="sortable-item">
        <?php

        $elementsArray = get_table_options(
                'Element', null,
                    array(
                        'sort' => 'alpha',
                        'item_type_id' => $item_type_id
                    )
                );
        echo $this->formSelect(
            $element_id_name, $element_id_value,
            array('class' => 'existing-element-drop-down'), $elementsArray );
        echo "<span>" . __("Prompt:") . "</span>";
        echo $this->formText($element_prompt_name, $element_prompt_value, array('class'=>'prompt'));
        ?>
        <span class='long-text'><?php echo __('Multiple rows'); ?></span>
        <?php echo $this->formCheckbox($element_long_name, null);    ?>        
        <?php
        echo $this->formHidden(
            $element_order_name, $element_order_value,
            array('class' => 'element-order')
        );
        ?>
        <a href="" class="delete-element"><?php echo __('Remove'); ?></a>
    </div>
    <div class="drawer-contents"></div>
</li>
