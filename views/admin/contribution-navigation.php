<nav id="section-nav" class="navigation vertical">
<?php
    
    if(is_allowed('Contribution_Types', 'edit')) {
        $navArray = array(
        'Dashboard' => array('label'=>'Dashboard', 'uri'=>url('contribution/index') ),
        'Contribution Types' => array('label'=>'Contribution Types', 'uri'=>url('contribution/types') ),
        'Submission Settings' => array('label'=> 'Submission Settings', 'uri'=>url('contribution/settings') ) 
        );
        
    } else {
        $navArray = array();
    }    
    
    $navArray['Contributors'] = array('label'=> 'Contributions', 
                                        'uri'=>url('contribution/items') );
 
    echo nav($navArray, 'contribution_navigation');
?>
</nav>