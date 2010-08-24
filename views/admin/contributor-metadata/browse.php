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
    <form method="POST">
    <table>
        <thead id="contributor-metadata-table-head">
            <tr>
                <th>Name</th>
                <th>Prompt</th>
                <th>Type</th>
            </tr>
        </thead>
        <tfoot id="contributor-fields-table-foot">
            <td colspan="3"><?php echo $this->formSubmit('add-prompt', 'Add a Prompt', array('class' => 'add-element')); ?></td>
        </tfoot>
        <tbody id="contributor-fields-table-body">
<?php foreach ($contributioncontributorfields as $id => $field): ?>
    <tr>
        <td><?php echo html_escape($field['name']); ?></td>
        <td><?php echo html_escape($field['prompt']); ?></td>
        <td></td>
    </tr>
<?php endforeach; ?>
        </tbody>
    </table>
    </form>
</div>
<?php
    echo js('jquery');
    echo js('contribution');
?>
<script type="text/javascript">
    
</script>
<?php foot();