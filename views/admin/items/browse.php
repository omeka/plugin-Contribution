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

    
    <?php 
    $db = get_db();
    $contributors = $db->getTable('ContributionContributedItem')->findAllContributors();
    asort($contributors);
    
    ?>
    <ul class="quick-filter-wrapper">
        <li><a href="#" tabindex="0"><?php echo __('Filter by contributor'); ?></a>
        
        <ul class="dropdown">
            <li><a href="<?php echo url('contribution/items'); ?>"><?php echo __('View All') ?></a></li>
            <?php foreach($contributors as $id=>$name): ?>
            <li><a href="<?php echo url('contribution/items', array('contributor' => $id)); ?>"><?php echo $name; ?></a></li>
            <?php endforeach; ?>
        </ul>
        
    </ul>
    
    
    <ul class="quick-filter-wrapper">
        <li><a href="#" tabindex="0"><?php echo __('Filter by status'); ?></a>
        <ul class="dropdown">
            <li><span class="quick-filter-heading"><?php echo __('Filter by status') ?></span></li>
            <li><a href="<?php echo url('contribution/items'); ?>"><?php echo __('View All') ?></a></li>
            <li><a href="<?php echo url('contribution/items', array('status' => 'public')); ?>"><?php echo __('Public'); ?></a></li>
            <li><a href="<?php echo url('contribution/items', array('status' => 'private')); ?>"><?php echo __('Private'); ?></a></li>
            <li><a href="<?php echo url('contribution/items', array('status' => 'review')); ?>"><?php echo __('Needs review'); ?></a></li>
        </ul>
        </li>
    </ul>    
    
    <table>
        <thead id="types-table-head">
            <tr>
                <?php
                $browseHeadings[__('Contributor')] = 'contributor';
                $browseHeadings[__('Item')] = null;
                $browseHeadings[__('Publication Status')] = null;
                $browseHeadings[__('Date Added')] = 'added';
                echo browse_sort_links($browseHeadings, array('link_tag' => 'th scope="col"', 'list_tag' => '')); 
                ?>        
            </tr>
            
        </thead>
        <tbody id="types-table-body">
        <?php foreach(loop('contribution_contributed_items') as $contribItem):?>
        
        <?php $item = $contribItem->Item; ?>
        <?php $contributor = $contribItem->Contributor; ?>
        <?php 
            if($contributor->id) {
                $contributorUrl = url('contribution/contributors/id/' . $contributor->id);
            }
        
        ?>
        <tr>

            <td><?php echo metadata($contributor, 'name');?> <a href=''>View info and contributions</a></td>
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