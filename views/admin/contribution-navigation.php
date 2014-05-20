<nav id="section-nav" class="navigation vertical">
<?php echo nav(array(
    'Dashboard' => array('label'=>__('Dashboard'), 'uri'=>url('contribution/index') ),
    'Contribution Types' => array('label'=>__('Contribution Types'), 'uri'=>url('contribution/types') ),
    'Contributor Questions' =>array('label'=> __('Contributor Questions'), 'uri'=>url('contribution/contributor-metadata') ),
    'Submission Settings' => array('label'=> __('Submission Settings'), 'uri'=>url('contribution/settings') ),
    'Contributors' => array('label'=> __('Contributors'), 'uri'=>url('contribution/contributors') )
    ), 'contribution_navigation');
?>
</nav>