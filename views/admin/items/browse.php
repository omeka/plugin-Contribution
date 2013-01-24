<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */

contribution_admin_header(array('Contributed Items'));
?>


<?php 
echo $this->partial('contribution-navigation.php');
?>

<div id="primary">

<?php
echo flash();
if (!has_loop_records('contribution_contributed_items')):
    echo '<p>No one has contributed to the site yet.</p>';
else:
?>
    <div class="pagination"><?php echo pagination_links(); ?></div>
    <table>
        <thead id="types-table-head">
            <tr>
                <th>Contributor</th>
                <th>Item</th>
                <th>Publication Status</th>
                <th>Date Contributed</th>
            </tr>
        </thead>
        <tbody id="types-table-body">
        <?php foreach(loop('contribution_contributed_items') as $contribItem):?>
        
        <?php $item = $contribItem->Item; ?>
        <?php $contributor = $contribItem->Contributor; ?>
        <tr>
            <td><?php echo metadata($contributor, 'name');?> <a href=''>View contributions</a></td>
            <td><?php echo link_to($item, 'show', metadata($item, array('Dublin Core', 'Title'))); ?></td>
            <?php 
                if ($item->public) {
                    $status = 'Public';
                } else {
                    if($contribItem->public) {
                        $status = 'Needs review';
                    } else {
                        $status = 'Private contribution';
                    }
                }
            ?>
            <td><?php echo $status; ?></td>
            <td><?php echo metadata($item, 'added'); ?>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
</div>

<?php echo foot(); ?>