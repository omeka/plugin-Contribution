<?php 
    $itemTypeOptions = get_db()->getTable('ContributionType')->getPossibleItemTypes();
    $itemTypeOptions = array('' => 'Select an Item Type') + $itemTypeOptions;
?>
<form method='post'>  
<section class='seven columns alpha'>
<?php if($action == 'add'): ?>
    <div class="field">
        <div class="two columns alpha">
            <label><?php echo __("Item Type"); ?></label>
        </div>
        <div class="inputs five columns omega">
            <p class="explanation"><?php echo __("The Item Type, from your site's list of types, you would like to use."); ?></p>
            <div class="input-block">
               <?php echo $this->formSelect('item_type_id', $contribution_type->item_type_id, array(), $itemTypeOptions); ?>
            </div>
        </div>
     </div>
    <?php else: ?>
        <input type="hidden" id="item_type_id" value="<?php echo $contribution_type->item_type_id; ?>"/>
    <?php endif; ?>

    <div class="field">
        <div class="two columns alpha">
            <label><?php echo __("Display Name"); ?></label>
        </div>
        <div class="inputs five columns omega">
            <p class="explanation"><?php echo __("The label you would like to use for this contribution type. If blank, the Item Type name will be used."); ?></p>
            <div class="input-block">
             <?php echo $this->formText('display_name', $contribution_type->display_name, array()); ?>
            </div>
        </div>
     </div>

     <div class="field">
        <div class="two columns alpha">
            <label><?php echo __("Allow File Upload Via Form"); ?></label>
        </div>
        <div class="inputs five columns omega">
            <p class="explanation"><?php echo __("Enable or disable file uploads through the public contribution form. If set to &#8220;Required,&#8220; users must add a file to their contribution when selecting this item type."); ?></p>
            <div class="input-block">
               <?php echo $this->formSelect('file_permissions', __('%s', $contribution_type->file_permissions), array(), ContributionType::getPossibleFilePermissions()); ?>
            </div>
        </div>
     </div>  
    

    
    <div id="element-list" class="seven columns alpha">
        <ul id="contribution-type-elements" class="sortable">
        <?php
        foreach ($contributionTypeElements as $contributionElement):
            if ($contributionElement):
        ?>
        
            <li class="element">
                <div class="sortable-item">
                <strong><?php echo html_escape($contributionElement->Element->name); ?></strong><span class='prompt'><?php echo __('Prompt'); ?></span>
                <?php echo $this->formText("elements[$contributionElement->id][prompt]" , $contributionElement->prompt); ?>
                <span class='long-text'><?php echo __('Multiple rows'); ?></span>
                <?php echo $this->formCheckbox("elements[$contributionElement->id][long_text]", null, array('checked'=>$contributionElement->long_text));    ?>
                <?php echo $this->formHidden("elements[$contributionElement->id][order]", $contributionElement->order, array('size'=>2, 'class' => 'element-order')); ?>
                <?php if (is_allowed('Contribution_Types', 'delete-element')): ?>
                <a id="return-element-link-<?php echo html_escape($contributionElement->id); ?>" href="" class="undo-delete"><?php echo __('Undo'); ?></a>
                <a id="remove-element-link-<?php echo html_escape($contributionElement->id); ?>" href="" class="delete-element"><?php echo __('Remove'); ?></a>
                <?php endif; ?>
                </div>
                
                <div class="drawer-contents">
                    <div class="element-description"><?php echo html_escape($contributionElement->Element->description); ?></div>
                </div>
            </li>
            <?php else: ?>
                <?php if (!$contributionElement->exists()):  ?>
                <?php echo $this->action(
                    'add-new-element', 'contribution-types', null,
                    array(
                        'from_post' => true,
                        'elementTempId' => $elementTempId,
                        'elementName' => $element->name,
                        'elementDescription' => $element->description,
                        'elementOrder' => $elementOrder
                    )
                );
                ?>
                <?php else: ?>
                <?php echo $this->action(
                    'add-existing-element', 'contribution-types', null,
                    array(
                        'from_post' => true,
                        'elementTempId' => $elementTempId,
                        'elementId' => $element->id,
                        'elementOrder' => $elementOrder
                    )
                );
                ?>
                <?php endif; ?>
            <?php endif; ?>
        <?php endforeach; // end for each $elementInfos ?> 
            <li>
                <div class="add-new">
                    <?php echo __('Add Element'); ?>
                </div>
                <div class="drawer-contents">
                    <button id="add-element" name="add-element"><?php echo __('Add Element'); ?></button>
                </div>
            </li>
        </ul>
        <?php echo $this->formHidden('elements_to_remove'); ?>
    </div>
</section>

<section class='three columns omega'>
    <div id='save' class='panel'>
            
            <input type="submit" class="big green button" value="<?php echo __('Save Changes');?>" id="submit" name="submit">
            <?php if($contribution_type->exists()): ?>
            <?php echo link_to($contribution_type, 'delete-confirm', __('Delete'), array('class' => 'big red button delete-confirm')); ?>
            <?php endif; ?>
    </div>
</section>
</form>