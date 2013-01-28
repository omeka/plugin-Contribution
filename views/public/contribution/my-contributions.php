<?php echo head(); ?>
<div id="primary">

<form method='post'>
    <table>
        <thead>
            <tr>
                <th>Make public?</th>
                <th>Item</th>
                <th>Added</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach(loop('contrib_items') as $contribItem): ?>
            <?php $item = $contribItem->Item; ?>
            <tr>
                <td><?php echo $this->formCheckbox("contribution_public[{$contribItem->id}]", null, array('checked'=>$contribItem->public) ); ?>
                </td>
                <td><?php echo link_to($item, 'show', metadata($item, array('Dublin Core', 'Title'))); ?></td>
                <td><?php echo metadata($item, 'added'); ?></td>
            
            </tr>
            
            <?php endforeach; ?>
        </tbody>
    </table>
    <input id="save-changes" class="submit big button" type="submit" value="Save Changes" name="submit">
</form>
</div>
<?php echo foot(); ?>
