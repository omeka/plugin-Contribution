<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */

contribution_admin_header(array('Contributor Questions'));
?>
<?php 
echo $this->partial('contribution-navigation.php');
?>

<p id="add-field" class="add-button">
    <a class="small green button" href="<?php echo url(array('action' => 'add')); ?>"><?php echo __('Add a Question'); ?></a>
</p>

<div id="primary">
    <?php echo flash(); ?>
    <div class="table-responsive">
    <table>
        <thead id="contributor-fields-table-head">
            <tr>
                <th>Question</th>
                <th>Type</th>
                <th>Edit</th>
            </tr>
        </thead>
        <tbody id="contributor-fields-required">
            <tr>
                <td>Name</td>
                <td rowspan="2" colspan="2" style="background-color: #eee; vertical-align: middle; text-align: center;">These questions are required by the plugin and cannot be edited.</td>
            </tr>
            <tr>
                <td>Email Address</td>
            </tr>
        </tbody>
        <tbody id="contributor-fields-sortable">
<?php if(isset($contribution_contributor_fields)): ?>
<?php foreach ($contribution_contributor_fields as $field): ?>
    <tr>
        <td><?php echo html_escape($field['prompt']); ?></td>
        <td><?php echo html_escape($field['type']); ?></td>
        <td><a href="<?php echo url(array('action' => 'edit', 'id' => $field['id'])); ?>" class="edit">Edit</a></td>
    </tr>
<?php endforeach; ?>
<?php endif; ?>
        </tbody>
    </table>
    </div>
</div>
<?php echo foot();
