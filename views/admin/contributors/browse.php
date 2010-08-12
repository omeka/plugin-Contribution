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
<?php foreach ($browseData as $id => $contributorInfo): ?>
    <tr>
        <td><a href="<?php echo uri(array('action' => 'show', 'id' => $id)); ?>"><?php echo html_escape($contributorInfo['name']); ?></a></td>
        <td><?php echo html_escape($contributorInfo['email']); ?></td>
        <td><a href="<?php echo uri("items/browse/contributor_id/$id") ?>"><?php echo html_escape($contributorInfo['item_count']); ?></a></td>
    </tr>
<?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php foot();