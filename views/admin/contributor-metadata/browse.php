<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */

contribution_admin_header(array('Contributor Questions'));
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
                <th>Question</th>
                <th>Type</th>
                <th>Edit</th>
                <th>Delete</th>
            </tr>
        </thead>
        <tbody id="contributor-fields-table-body">
            <tr style="background-color: lightgray;">
                <td>Name</td>
                <td rowspan="2" colspan="4" style="vertical-align: middle;">These questions are required by the plugin and cannot be edited.</td>
            </tr>
            <tr style="background-color: lightgray;">
                <td>Email Address</td>
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