<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2013
 * @package Contribution
 */

queue_css_file('contribution-browse');
queue_js_file('contribution-contributed-item');
queue_js_file('contribution-browse');

contribution_admin_header(array(__('Contributed Items (%d)', $total_results)));

// To avoid to determine rights for each record.
$allowToManage = (is_allowed('Items', 'edit') || is_allowed('Items', 'update') || is_allowed('Items', 'delete'));

echo $this->partial('contribution-navigation.php');
?>

<div id="primary">

<?php
echo flash();

if (!Omeka_Captcha::isConfigured()): ?>
    <p class="alert"><?php echo __("You have not entered your %s API keys under %s. We recommend adding these keys, or the contribution form will be vulnerable to spam.", '<a href="http://recaptcha.net/">reCAPTCHA</a>', "<a href='" . url('settings/edit-security#fieldset-captcha') . "'>" . __('security settings') . "</a>");?></p>
<?php endif;
?>
<?php if ($total_results): ?>
    <div class="pagination"><?php echo pagination_links(); ?></div>

    <form action="<?php echo html_escape(url('contribution/index/batch-edit')); ?>" method="post" accept-charset="utf-8">
        <div class="table-actions batch-edit-option">
            <?php if (is_allowed('Items', 'edit') || is_allowed('Items', 'update')): ?>
            <input type="submit" class="small green batch-action button" name="submit-batch-approve" value="<?php echo __('Set public'); ?>">
            <?php endif; ?>
            <?php if (is_allowed('Items', 'edit') || is_allowed('Items', 'update')): ?>
            <input type="submit" class="small green batch-action button" name="submit-batch-proposed" value="<?php echo __('Set Needs review'); ?>">
            <?php endif; ?>
            <?php if (is_allowed('Items', 'edit') || is_allowed('Items', 'delete')): ?>
            <input type="submit" class="small red batch-action button" name="submit-batch-delete" value="<?php echo __('Delete'); ?>">
            <?php endif; ?>
        </div>

        <?php echo common('contribution-quick-filters'); ?>

        <table id="contributions" cellspacing="0" cellpadding="0">
        <thead id="types-table-head">
            <tr>
                <?php if ($allowToManage): ?>
                <th class="batch-edit-heading"><?php echo __('Select'); ?></th>
                <?php endif;
                $browseHeadings[__('Item')] = null;
                $browseHeadings[__('Contributor')] = 'contributor';
                if($allowToManage) {
                    $browseHeadings[__('Publication Status')] = null;
                } else {
                    $browseHeadings[__('Publication Status')] = null;
                }
                $browseHeadings[__('Date Added')] = 'added';
                echo browse_sort_links($browseHeadings, array('link_tag' => 'th scope="col"', 'list_tag' => ''));
                ?>
            </tr>
        </thead>
        <tbody id="types-table-body">
            <?php
            $key = 0;
            foreach(loop('contribution_contributed_items') as $contributedItem):
                $item = $contributedItem->Item;
                $contributor = $contributedItem->Contributor;
                if ($contributor->id) {
                    $contributorUrl = url('contribution/contributors/show/id/' . $contributor->id);
                }
                if ($item->public) {
                    $status = 'approved';
                    if($allowToManage) {
                        $statusText = __('Public (click to put in review)');
                    } else {
                        $statusText = __('Public');
                    }
                } else {
                    if ($contributedItem->public) {
                        $status = 'proposed';
                        if($allowToManage) {
                            $statusText = __('Needs review (click to make public)');
                        } else {
                            $statusText = __('Needs review');
                        }
                    }
                    else {
                        $status = 'private';
                        $statusText = __('Private contribution');
                    }
                } ?>
            <tr class="contribution <?php if(++$key%2==1) echo 'odd'; else echo 'even'; ?>">
                <?php if ($allowToManage): ?>
                <td class="batch-edit-check" scope="row">
                    <?php if ($status == 'private'): ?>
                    <span><?php echo $statusText; ?></span>
                    <?php else: ?>
                    <input type="checkbox" name="contributions[]" value="<?php echo $contributedItem->id; ?>" />
                    <?php endif; ?>
                </td>
                <?php endif; ?>
                <td class="record-info"><?php
                    echo link_to($item, 'show', metadata($item, array('Dublin Core', 'Title')));
                    if (metadata($item, 'has thumbnail')):
                        echo link_to_item(item_image('square_thumbnail', array(), 0, $item), array('class' => 'item-thumbnail'), 'show', $item);
                    endif;
                ?></td>
                <td class="contributor"><?php echo metadata($contributor, 'name');?>
                    <?php if (!is_null($contributor->id)):
                        if ($contributedItem->anonymous && (is_allowed('Contribution_Items', 'view-anonymous') || $contributor->id == current_user()->id)): ?>
                    <span>(<?php echo __('Anonymous'); ?>)</span>
                        <?php endif; ?>
                    <ul class="action-links group">
                       <li><a href='<?php echo $contributorUrl; ?>'><?php echo __("Info and contributions"); ?></a></li>
                    </ul>
                    <?php endif; ?>
                </td>
                <td class="contribution-status">
                    <?php if ($allowToManage && ($status != 'private')): ?>
                    <a href="<?php echo ADMIN_BASE_URL; ?>" id="contribution-<?php echo $contributedItem->id; ?>" class="contribution toggle-status status <?php echo $status; ?>"><?php echo $statusText; ?></a>
                    <?php else: ?>
                    <span class="contribution toggle-status status <?php echo $status; ?>"><?php echo $statusText; ?></span>
                    <?php endif; ?>
                </td>
                <td class="contribution-date"><?php echo format_date(metadata($item, 'added'), Zend_Date::DATETIME_MEDIUM); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        </table>

        <div class="table-actions batch-edit-option">
            <?php if (is_allowed('Items', 'edit') || is_allowed('Items', 'update')): ?>
            <input type="submit" class="small green batch-action button" name="submit-batch-approve" value="<?php echo __('Set public'); ?>">
            <?php endif; ?>
            <?php if (is_allowed('Items', 'edit') || is_allowed('Items', 'update')): ?>
            <input type="submit" class="small green batch-action button" name="submit-batch-proposed" value="<?php echo __('Set Needs review'); ?>">
            <?php endif; ?>
            <?php if (is_allowed('Items', 'edit') || is_allowed('Items', 'delete')): ?>
            <input type="submit" class="small red batch-action button" name="submit-batch-delete" value="<?php echo __('Delete'); ?>">
            <?php endif; ?>
        </div>

        <?php echo common('contribution-quick-filters'); ?>
    </form>

    <div class="pagination"><?php echo pagination_links(); ?></div>

    <script type="text/javascript">
        Omeka.messages = jQuery.extend(Omeka.messages,
            {'contribution':{
                'proposed':<?php echo json_encode(__('Needs review (click to make public)')); ?>,
                'approved':<?php echo json_encode(__('Public (click to put in review)')); ?>,
                'private':<?php echo json_encode(__('Private')); ?>,
                'rejected':<?php echo json_encode(__('Rejected')); ?>,
                'confirmation':<?php echo json_encode(__('Are you sure you want to remove these contributions?')); ?>
            }}
        );
        Omeka.addReadyCallback(Omeka.ContributionBrowse.setupBatchEdit);
    </script>

<?php else: ?>
    <?php if (total_records('ContributionContributedItem') == 0): ?>
    <h2><?php echo __('There is no contribution yet.'); ?></h2>
    <?php else: ?>
    <p><?php echo __('The query searched %d contributions and returned no results.', total_records('ContributionContributedItem')); ?></p>
    <p><a href="<?php echo url('contribution/items'); ?>"><?php echo __('See all contributions.'); ?></a></p>
    <?php endif; ?>
<?php endif; ?>
</div>
<?php echo foot(); ?>
