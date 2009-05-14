<?php head(); ?>
<script type="text/javascript" charset="utf-8">
    Event.observe(window, 'load', function(){
        $('check-all').observe('click', function(e){
            e.stop();
            var checkboxes = $$('input[type="checkbox"]');
            if (this.innerHTML == 'Check All') {
                checkboxes.invoke('setAttribute', 'checked', true);
                this.update('Uncheck All');
            } else {
                checkboxes.invoke('removeAttribute', 'checked');
                this.update('Check All');
            };
        });
    });
</script>

<div id="primary">
	
	<?php echo flash(); ?>
	
	<h2>Browse Contributors (<?php echo $total_records; ?> total)</h2>

    <div class="pagination">
        <?php echo pagination_links(); ?>
    </div>
    
    <form class="clear" action="<?php echo uri(array('action'=>'batch', 'controller'=>'index', 'module'=>'contribution'), 'default', array(), true); ?>" method="post" accept-charset="utf-8">
    
	<table class="browse-list simple vertical-headings" cellspacing="0" cellpadding="0">
		<thead>
			<tr>
			    <th><a href="#" id="check-all">Check All</a></th>
				<th>Contributor Name</th>
				<th colspan="2">Information</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ($contributors as $contributor): ?>
			<tr>
				<td rowspan="7" style="width:12%;"><?php echo $this->formCheckbox('contributor_id[]', $contributor->id); ?></td>
				<th scope="row" rowspan="7"><a href="<?php echo uri('items/browse', array('contributor'=>$contributor->id)); ?>" title="View items contributed by <?php echo html_escape($contributor->name); ?>."><?php echo html_escape($contributor->name); ?></a></th>
				<th scope="row">Email</th>
				<td><?php echo html_escape($contributor->email); ?></td>
			</tr>
			<tr>
				<th scope="row">Race</th>
				<td><?php echo html_escape($contributor->race); ?></td>
			</tr>
			<tr>
				<th>Gender</th>
				<td><?php echo html_escape($contributor->gender); ?></td>
			</tr>
			<tr>
				<th>Occupation</th>
				<td><?php echo html_escape($contributor->occupation); ?></td>	
			</tr>
			<tr>
				<th>Zipcode</th>
				<td><?php echo html_escape($contributor->zipcode); ?></td>
			</tr>
			<tr>
				<th>Birth Year</th>
				<td><?php echo html_escape($contributor->birth_year); ?></td>
			</tr>
			<tr>
				<th>IP Address</th>
				<td><?php echo html_escape($contributor->ip_address); ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
		
	</table>
	
	<fieldset>
	    <?php echo $this->formSelect('batch_action', null, null, array(''=>'Choose Action', 'delete'=>'Delete')); ?>
	    <?php echo $this->formSubmit('submit', 'Submit'); ?>
	</fieldset>
	
	</form>
</div>

<?php foot(); ?>