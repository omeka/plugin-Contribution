<nav id="section-nav" class="navigation vertical">
<?php
    
    if(is_allowed('Contribution_Types', 'edit')) {
        $navArray = array(
        'Getting Started' => array('label'=>__('Getting Started'), 'uri'=>url('contribution/index') ),
        'Contribution Types' => array('label'=>__('Contribution Types'), 'uri'=>url('contribution/types') ),
        'Submission Settings' => array('label'=> __('Submission Settings'), 'uri'=>url('contribution/settings') ) 
        );
        
    } else {
        $navArray = array();
    }    
    
    $navArray['Contributors'] = array('label'=> __('Contributions'), 
                                        'uri'=>url('contribution/items?sort_field=added&sort_dir=d') );
 
    echo nav($navArray, 'contribution_navigation');
?>
</nav>