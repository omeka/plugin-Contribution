<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */

contribution_admin_header(array('Contributors'));
?>
<div id="primary">
    <?php echo flash(); ?>
    <table>
        <thead id="types-table-head">
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Contributed Items</th>
            </tr>
        </thead>
        <tbody id="types-table-body">
<?php foreach ($browseData as $contributorInfo): ?>
    <tr>
        <td><?php echo html_escape($contributorInfo['name']); ?></td>
        <td><?php echo html_escape($contributorInfo['email']); ?></td>
        <td><?php echo html_escape($contributorInfo['item_count']); ?></td>
    </tr>
<?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php foot();