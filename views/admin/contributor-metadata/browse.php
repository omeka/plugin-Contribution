<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */

contribution_admin_header(array('Contributor Metadata'));
?>
<p id="add-field" class="add-button">
    <a class="add" href="<?php echo uri(array('action' => 'add')); ?>">Add a Question</a>
</p>

<div id="primary">
    <?php echo flash(); ?>
    <table>
        <thead id="contributor-fields-table-head">
            <tr>
                <th>Name</th>
                <th>Prompt</th>
                <th>Type</th>
                <th>Edit</th>
                <th>Delete</th>
            </tr>
        </thead>
        <tbody id="contributor-fields-table-body">
            <tr>
                <td colspan="5">Name</td>
            </tr>
            <tr>
                <td colspan="5">Email Address</td>
            </tr>
<?php foreach ($contributioncontributorfields as $field): ?>
    <tr>
        <td><?php echo html_escape($field['name']); ?></td>
        <td><?php echo html_escape($field['prompt']); ?></td>
        <td><?php echo html_escape($field['type']); ?></td>
        <td><a href="<?php echo uri(array('action' => 'edit', 'id' => $field['id'])); ?>" class="edit">Edit</a></td>
        <td><?php echo delete_button(uri(array('action' => 'delete', 'id' => $field->id)), "delete-field-{$field->id}", 'Delete'); ?></td>
    </tr>
<?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php foot();