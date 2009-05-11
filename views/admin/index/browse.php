<?php head(); ?>

<div id="primary">
	
	<?php echo flash(); ?>
	
	<h2>Browse Contributors (<?php echo $total_records; ?> total)</h2>

    <div class="pagination">
        <?php echo pagination_links(); ?>
    </div>
    
    <form action="<?php echo uri(array('action'=>'batch', 'controller'=>'index', 'module'=>'contribution'), 'default', array(), true); ?>" method="post" accept-charset="utf-8">
        
    
	<table>
		<thead>
			<tr>
			    <th>&nbsp;</th>
				<th>Name</th>
				<th>Email</th>
				<th>Race</th>
				<th>Gender</th>
				<th>Occupation</th>
				<th>Zipcode</th>
				<th>Birth Year</th>
				<th>IP Address</th>
				<th>Contribution(s)</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ($contributors as $contributor): ?>
			<tr>
			    <td><?php echo $this->formCheckbox('contributor_id[]', $contributor->id); ?></td>
				<td><?php echo html_escape($contributor->name); ?></td>
				<td><?php echo html_escape($contributor->email); ?></td>
				<td><?php echo html_escape($contributor->race); ?></td>
				<td><?php echo html_escape($contributor->gender); ?></td>
				<td><?php echo html_escape($contributor->occupation); ?></td>
				<td><?php echo html_escape($contributor->zipcode); ?></td>
				<td><?php echo html_escape($contributor->birth_year); ?></td>
				<td><?php echo html_escape($contributor->ip_address); ?></td>
				<td><a href="<?php echo uri('items/browse', array('contributor'=>$contributor->id)); ?>">View</a></td>
			</tr>
		<?php endforeach ?>
		</tbody>
		
	</table>
	
	<fieldset>
	    <?php echo $this->formSelect('batch_action', null, null, array(''=>'Choose Action', 'delete'=>'Delete')); ?>
	    <?php echo $this->formSubmit('submit', 'Submit'); ?>
	</fieldset>
	
	</form>
</div>

<?php foot(); ?>