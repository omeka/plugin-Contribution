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
            <label id="display-name-label"><?php echo __("Display Name"); ?></label>
        </div>
        <div class="inputs five columns omega">
            <p class="explanation"><?php echo __("The label you would like to use for this contribution type. If blank, the Item Type name will be used."); ?></p>
            <div class="input-block">
             <?php echo $this->formText('display_name', $contribution_type->display_name, array('aria-labelledby' => 'display-name-label')); ?>
            </div>
        </div>
     </div>

     <div class="field">
        <div class="two columns alpha">
            <label id="file-permissions-label"><?php echo __("Allow File Upload Via Form"); ?></label>
        </div>
        <div class="inputs five columns omega">
            <p class="explanation"><?php echo __("Enable or disable file uploads through the public contribution form. If set to &#8220;Required,&#8220; users must add a file to their contribution when selecting this item type."); ?></p>
            <div class="input-block">
               <?php echo $this->formSelect('file_permissions', __('%s', $contribution_type->file_permissions), array('aria-labelledby' => 'file-permissions-label'), ContributionType::getPossibleFilePermissions()); ?>
            </div>
        </div>
     </div>  
    

    
    <div id="element-list">
        <ul id="contribution-type-elements" class="sortable">
        <?php foreach ($contributionTypeElements as $contributionElement): ?>
            <?php if ($contributionElement): ?>
            <?php 
                $contributionElementId = $contributionElement->id; 
                $contributionElementName = $contributionElement->Element->name;
            ?>
            <li class="element" id="<?php echo html_escape($contributionElementId); ?>-group" role="group">
                <div class="sortable-item drawer">
                <strong class="drawer-name"><?php echo html_escape($contributionElementName); ?></strong>
                <?php if (is_allowed('Contribution_Types', 'delete-element')): ?>
                <button type="button" id="return-element-link-<?php echo html_escape($contributionElementId); ?>" aria-expanded="false" aria-controls="<?php echo html_escape($contributionElementId); ?>-group" aria-label="<?php echo __('Undo %s removal', html_escape($contributionElementName)); ?>" class="undo-delete" data-action-selector="deleted" title="<?php echo __('Undo %s removal', html_escape($contributionElementName)); ?>"><span class="icon" aria-hidden="true"></span></button>
                <button type="button" id="remove-element-link-<?php echo html_escape($contributionElementId); ?>" aria-expanded="true" aria-controls="<?php echo html_escape($contributionElementId); ?>-group" class="delete-drawer" data-action-selector="deleted" title="<?php echo __('Remove %s', html_escape($contributionElementName)); ?>" aria-label="<?php echo __('Remove %s', html_escape($contributionElementName)); ?>"><span class="icon" aria-hidden="true"></span></button>
                <?php endif; ?>
                </div>
                
                <div class="drawer-contents opened">
                    <div class="element-description"><?php echo html_escape($contributionElement->Element->description); ?></div>
                    <?php 
                        echo $this->formHidden("elements[$contributionElementId][order]", $contributionElement->order, array(
                            'size'=>2, 
                            'class' => 'element-order'
                        )); 
                    ?>
                    <div class="field">
                        <div class="field-meta"><label class="prompt" id="elements[<?php echo $contributionElementId; ?>][prompt]-label"><?php echo __('Prompt'); ?></label></div>
                        <div class="inputs"><?php echo $this->formText("elements[$contributionElementId][prompt]" , $contributionElement->prompt, array('aria-labelledby' => "elements[$contributionElementId][prompt]-label")); ?></div>
                    </div>
                    <div class="field">
                        <div class="field-meta"><label class='long-text' id="elements[<?php echo $contributionElementId; ?>][long_text]-label"><?php echo __('Multiple rows'); ?></label></div>
                        <div class="inputs">
                            <?php 
                            echo $this->formCheckbox("elements[$contributionElementId][long_text]", null, array(
                                'checked'=>$contributionElement->long_text, 
                                'aria-labelledby' => "elements[$contributionElementId][long_text]-label"
                            )); 
                            ?>
                        </div>
                    </div>
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
            <li id="new-element-item">
                <div class="add-new">
                    <?php echo __('Add Element'); ?>
                </div>
                <div class="drawer-contents opened">
                    <button type="button" id="add-element" name="add-element"><?php echo __('Add Element'); ?></button>
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
<script>
Omeka.manageDrawers('#element-list');
</script>