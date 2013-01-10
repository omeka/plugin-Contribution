<?php echo js_tag('contribution-types'); ?>
<script type="text/javascript">
jQuery(document).ready(function () {
    var addNewRequestUrl = '<?php echo admin_url('contribution/types/add-new-element'); ?>';
    var addExistingRequestUrl = '<?php echo admin_url('contribution/types/add-existing-element'); ?>';
    var changeExistingElementUrl = '<?php echo admin_url('contribution/types/change-existing-element'); ?>';
    Omeka.ContributionTypes.manageContributionTypes(addNewRequestUrl, addExistingRequestUrl, changeExistingElementUrl);
    Omeka.ContributionTypes.enableSorting();
});
</script>


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

