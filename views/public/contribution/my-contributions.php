<?php
$title = __('My Contributions');
$bodyClass = 'contribution';

echo head(array(
    'title' => $title,
    'bodyclass' => $bodyClass,
)); ?>
<div id="primary">
<?php echo flash(); ?>
    <h1><?php echo $title; ?></h1>
<form method='post'>
    <table>
        <thead>
            <tr>
                <th><?php echo __('Public'); ?></th>
                <th><?php echo __('Anonymous'); ?></th>
                <th><?php echo __('Item'); ?></th>
                <?php if (is_allowed('Contribution_Contribution', 'edit')): ?>
                <th><?php echo __('Edit'); ?></th>
                <th><?php echo __('Remove'); ?></th>
                <?php endif; ?>
                <th><?php echo __('Added'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach(loop('contrib_items') as $contribItem): ?>
            <?php $item = $contribItem->Item; ?>
            <tr>
                <td><?php echo $this->formCheckbox("contribution_public[{$contribItem->id}]",
                    null, array('checked' => $contribItem->public)); ?></td>
                <td><?php echo $this->formCheckbox("contribution_anonymous[{$contribItem->id}]",
                    null, array('checked' => $contribItem->anonymous)); ?></td>
                <td><?php echo link_to($item, 'show', metadata($item, array('Dublin Core', 'Title'))); ?></td>
                <?php if (is_allowed('Contribution_Contribution', 'edit')): ?>
                <td><?php echo contribution_link_to($contribItem, 'edit', __('Edit')); ?></td>
                <td><?php echo $this->formCheckbox("contribution_deleted[{$contribItem->id}]",
                    null, array('checked' => false)); ?></td>
                <?php endif; ?>
                <td><?php echo metadata($item, 'added'); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <input id="save-changes" class="submit big button" type="submit" value="Save Changes" name="submit">
</form>
</div>
<?php echo foot();
