

<section class='seven columns alpha'><input type="hidden" id="item_type_id" value="1"/>
    <div class="field">
        <div class="two columns alpha">
                        <label>Display Name</label>
                    </div>
                    <div class="inputs five columns omega">
                        <p class="explanation">The label you would like to use for this contribution type. If blank, the Item Type name will be used.</p>
                        <div class="input-block">
                         <input type="text" name="display_name" id="display_name" value="Story">
                        </div>
                    </div>
     </div>
     <div class="field">
        <div class="two columns alpha">
            <label>Allow File Upload Via Form</label>
        </div>
        <div class="inputs five columns omega">
            <p class="explanation">Enable or disable file uploads through the public contribution form. If set to &#8220;Required,&#8220; users must add a file to their contribution when selecting this item type.</p>
            <div class="input-block">
               <select name="file_permissions" id="file_permissions">
                    <option value="Disallowed">Disallowed</option>
                    <option value="Allowed" selected="selected">Allowed</option>
                    <option value="Required">Required</option>
                </select>
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
            <strong><?php echo html_escape($contributionElement->Element->name); ?></strong><span>Prompt: </span>
            <?php echo $this->formText("elements[$contributionElement->id][prompt]" , $contributionElement->prompt); ?>
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
                <p>
                    <input type="radio" name="add-element-type" value="existing" checked="checked" /><?php echo __('Existing'); ?>
                    <input type="radio" name="add-element-type" value="new" /><?php echo __('New'); ?>
                </p>
                <button id="add-element" name="add-element"><?php echo __('Add Element'); ?></button>
            </div>
        </li>
    </ul>
    <?php // echo $this->form->getElement(Omeka_Form_ItemTypes::REMOVE_HIDDEN_ELEMENT_ID); ?>
</div>                 
                 
                 
                 
 </section>
 <section class='three columns omega'>
     <div id='save' class='panel'>
         <input type="submit" class="big green button" value="Save" id="submit" name="submit">
         <a href="/Omeka/admin/contribution-types/delete-confirm/1" class="big red button delete-confirm">Delete</a>
     </div>
 </section>








