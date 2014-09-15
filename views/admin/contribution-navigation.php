<nav id="section-nav" class="navigation vertical">
<?php
    $navArray = array();

    if (is_allowed('Contribution_Types', 'edit')) {
        $navArray['Getting Started'] = array('label'=>__('Getting Started'), 'uri'=>url('contribution/index'));
        $navArray['Contribution Types'] = array('label'=>__('Contribution Types'), 'uri'=>url('contribution/types'));
    }

    $navArray['Contributors'] = array(
        'label'=> __('Contributions'),
        'uri'=>url('contribution/items?sort_field=added&sort_dir=d'),
    );

    echo nav($navArray, 'contribution_navigation');
?>
</nav>
