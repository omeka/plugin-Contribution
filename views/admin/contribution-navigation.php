<nav id="section-nav" class="navigation vertical">
<?php echo nav(array(
    'Dashboard' => array('label'=>'Dashboard', 'uri'=>url('contribution/index') ),
    'Contribution Types' => array('label'=>'Contribution Types', 'uri'=>url('contribution/types') ),
    'Submission Settings' => array('label'=> 'Submission Settings', 'uri'=>url('contribution/settings') ),
    'Contributors' => array('label'=> 'Contributions', 'uri'=>url('contribution/items') )
    ), 'contribution_navigation');
?>
</nav>