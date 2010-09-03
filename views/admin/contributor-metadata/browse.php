<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */

contribution_admin_header(array('Contributor Metadata'));
?>
<div id="primary">
    <?php echo flash(); ?>
    <form method="POST" action="<?php echo uri(array('action' => 'multiple-add')); ?>">
    <table>
        <thead id="contributor-metadata-table-head">
            <tr>
                <th>Name</th>
                <th>Prompt</th>
                <th>Type</th>
                <th>Edit</th>
                <th>Delete</th>
            </tr>
        </thead>
        <tfoot id="contributor-fields-table-foot">
            <tr>
                <td colspan="5"><?php echo $this->formSubmit('add-prompt', 'Add a Prompt', array('class' => 'add-element')); ?></td>
            </tr>
        </tfoot>
        <tbody id="contributor-fields-table-body">
<?php foreach ($contributioncontributorfields as $id => $field): ?>
    <tr>
        <td><?php echo html_escape($field['name']); ?></td>
        <td><?php echo html_escape($field['prompt']); ?></td>
        <td><?php echo html_escape($field['type']); ?></td>
        <td><a href="<?php echo uri(array('action' => 'edit', 'id' => $id)); ?>" class="edit">Edit</a></td>
        <td><a href="<?php echo uri(array('action' => 'delete', 'id' => $id)); ?>" class="delete">Delete</a></td>
    </tr>
<?php endforeach; ?>
        </tbody>
    </table>
        <?php echo $this->formSubmit('submit-changes', 'Submit Changes', array('class' => 'submit-button')); ?>
    </form>
</div>
<?php
    echo js('jquery');
    echo js('contribution');
?>
<script type="text/javascript">
    var newRow = <?php
        $nameInput = $this->formText('newFields[!!INDEX!!][name]', null, array('class' => 'textinput'));
        $promptInput = $this->formText('newFields[!!INDEX!!][prompt]', null, array('class' => 'textinput'));
        $dataTypeSelect = contribution_select_field_data_type('newFields[!!INDEX!!][type]');
        echo js_escape("<tr><td>$nameInput</td><td>$promptInput</td><td colspan=\"3\">$dataTypeSelect</td></tr>");
        ?>;
    setUpTableAppend('#add-prompt', '#contributor-fields-table-body', newRow);
</script>
<?php foot();