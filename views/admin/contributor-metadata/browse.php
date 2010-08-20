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
    <table>
        <thead id="types-table-head">
            <tr>
                <th>Name</th>
                <th>Prompt</th>
                <th>Type</th>
            </tr>
        </thead>
        <tbody id="types-table-body">
<?php foreach ($contributioncontributorfields as $id => $field): ?>
    <tr>
        <td><?php echo html_escape($field['name']); ?></td>
        <td><?php echo html_escape($field['prompt']); ?></td>
        <td></td>
    </tr>
<?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php foot();