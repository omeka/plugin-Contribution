<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */

contribution_admin_header(array(__('Contributors')));
?>


<?php 
echo $this->partial('contribution-navigation.php');
?>

<div id="primary">
<?php
echo flash();
if (!has_loop_records('contribution_contributors')):
    echo '<p>No one has contributed to the site yet.</p>';
else:
?>
    <div class="pagination"><?php echo pagination_links(); ?></div>
    <table>
        <thead id="types-table-head">
            <tr>
                <th><?php echo __('ID'); ?></th>
                <th><?php echo __('Name'); ?></th>
                <th><?php echo __('Email'); ?></th>
                <th><?php echo __('Contributed Items'); ?></th>
            </tr>
        </thead>
        <tbody id="types-table-body">
<?php 
foreach (loop('contribution_contributors') as $contributor):
    $id = $contributor->id;
?>
    <tr>
        <td><?php echo html_escape($contributor->id); ?></td>
        <td><a href="<?php echo url(array('action' => 'show', 'id' => $id)); ?>"><?php echo html_escape($contributor->name); ?></a></td>
        <td><?php echo html_escape($contributor->email); ?></td>
        <td><a href="<?php echo url("items/browse/contributor_id/$id") ?>">View</a></td>
    </tr>
<?php endforeach; ?>
        </tbody>
    </table>
    <div class="pagination"><?php echo pagination_links(); ?></div>
<?php endif; ?>
</div>
<?php echo foot(); ?>
