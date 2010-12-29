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
<?php
echo flash();
if (empty($contributioncontributors)):
    echo '<p>No one has contributed to the site yet.</p>';
else:
?>
    <div class="pagination"><?php echo pagination_links(); ?></div>
    <table>
        <thead id="types-table-head">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Contributed Items</th>
            </tr>
        </thead>
        <tbody id="types-table-body">
<?php 
foreach ($contributioncontributors as $contributor):
    $id = $contributor->id;
?>
    <tr>
        <td><?php echo html_escape($contributor->id); ?></td>
        <td><a href="<?php echo uri(array('action' => 'show', 'id' => $id)); ?>"><?php echo html_escape($contributor->name); ?></a></td>
        <td><?php echo html_escape($contributor->email); ?></td>
        <td><a href="<?php echo uri("items/browse/contributor_id/$id") ?>">View</a></td>
    </tr>
<?php endforeach; ?>
        </tbody>
    </table>
    <div class="pagination"><?php echo pagination_links(); ?></div>
<?php endif; ?>
</div>
<?php foot();
