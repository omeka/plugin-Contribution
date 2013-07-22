<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2013
 * @package Contribution
 */

contribution_admin_header(array(__('Contributed Items')));
?>


<?php 
echo $this->partial('contribution-navigation.php');
?>

<div id="primary">

<?php
echo flash();
?>
    <div class="pagination"><?php echo pagination_links(); ?></div>
    
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
                $browseHeadings[__('Item')] = null;
                $browseHeadings[__('Contributor')] = 'contributor';
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
                $contributorUrl = url('contribution/contributors/show/id/' . $contributor->id);
            }
        
        ?>
        <tr>
            <td><?php echo link_to($item, 'show', metadata($item, array('Dublin Core', 'Title'))); ?></td>
            <td><?php echo metadata($contributor, 'name');?>
                 
                 <?php if(!is_null($contributor->id)): ?>
                 <?php if($contribItem->anonymous && (is_allowed('Contribution_Items', 'view-anonymous') || $contributor->id == current_user()->id)): ?>
                 <span>(<?php echo __('Anonymous'); ?>)</span>
                 <?php endif; ?>
                 <ul class="action-links group">
                 <li><a href='<?php echo $contributorUrl; ?>'><?php echo __("Info and contributions"); ?></a></li>
                 </ul>
                 
                 <?php endif; ?>             
            </td>
            
            
            <?php 
                if ($item->public) {
                    $status = __('Public');
                } else {
                    if($contribItem->public) {
                        $status = __('Needs review');
                    } else {
                        $status = __('Private contribution');
                    }
                }
            ?>
            <td><?php echo $status; ?></td>
            <td><?php echo format_date(metadata($item, 'added')); ?>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<div class="pagination"><?php echo pagination_links(); ?></div>
<?php echo foot(); ?>