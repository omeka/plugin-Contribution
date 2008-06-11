<?php head(); ?>

<div id="primary">
	
	<h2>Browse a list of Contributors</h2>

	<table>
		<thead>
		<tr>
			<th>Name</th>
			<th>Email</th>
			<th>Race</th>
			<th>Gender</th>
			<th>Occupation</th>
			<th>Zipcode</th>
			<th>Birth Year</th>
			<th>IP Address</th>
			<th>View Contribution(s)</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($contributors as $contributor): ?>
			<tr>
				<td><?php echo h($contributor->name); ?></td>
				<td><?php echo h($contributor->email); ?></td>
				<td><?php echo h($contributor->race); ?></td>
				<td><?php echo h($contributor->gender); ?></td>
				<td><?php echo h($contributor->occupation); ?></td>
				<td><?php echo h($contributor->zipcode); ?></td>
				<td><?php echo h($contributor->birth_year); ?></td>
				<td><?php echo h($contributor->ip_address); ?></td>
				<td><a href="<?php echo uri('items/browse', array('entity'=>$contributor->id)); ?>">View</a></td>
			</tr>
		<?php endforeach ?>
	</tbody>
		
	</table>
</div>

<?php foot(); ?>