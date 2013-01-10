<nav id="section-nav" class="navigation vertical">
<?php echo nav(array(
    'Dashboard' => array('label'=>'Dashboard', 'uri'=>url('contribution/index') ),
    'Contribution Types' => array('label'=>'Contribution Types', 'uri'=>url('contribution/types') ),
    'Contributor Questions' =>array('label'=> 'Contributor Questions', 'uri'=>url('contribution/contributor-metadata') ),
    'Submission Settings' => array('label'=> 'Submission Settings', 'uri'=>url('contribution/settings') ),
    'Contributors' => array('label'=> 'Contributors', 'uri'=>url('contribution/contributors') )
    ), 'contribution_navigation');
?>
</nav>