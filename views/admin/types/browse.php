<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */
queue_css_file('contribution-type-form');
contribution_admin_header(array(__('Types')));
?>
<a id="add-type" class="small green button" href="<?php echo url(array('action' => 'add')); ?>"><?php echo __('Add a Type'); ?></a>
    
<?php 
echo $this->partial('contribution-navigation.php');
?>


<div id="primary">
    <?php echo flash(); ?>

    <table>
        <thead id="types-table-head">
            <tr>
                <th><?php echo __("Name"); ?></th>
                <th><?php echo __("Item Type"); ?></th>
                <th><?php echo __("Contributed Items"); ?></th>
                <th><?php echo __("File Upload"); ?></th>
                <th><?php echo __("Edit"); ?></th>
            </tr>
        </thead>
        <tbody id="types-table-body">
<?php foreach ($contribution_types as $type): ?>
    <tr>
        <td><strong><?php echo metadata($type, 'display_name'); ?></strong></td>
        <td><?php  echo __($type->ItemType->name); ?></td>
        <td><a href="<?php echo url('items/browse/contributed/1/type/' . $type->item_type_id); ?>"><?php echo __("View"); ?></a></td>
        <td><?php echo __(metadata($type, 'file_permissions')); ?></td>
        <td><a href="<?php echo url(array('action' => 'edit', 'id' => $type->id)); ?>" class="edit"><?php echo __("Edit"); ?></a></td>
    </tr>
<?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php echo foot(); ?>
