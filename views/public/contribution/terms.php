<?php
$title = __('Contribution Terms of Service');
$bodyClass = 'contribution';

echo head(array(
    'title' => $title,
    'bodyclass' => $bodyClass,
)); ?>
<div id="primary">
<h1><?php echo $title; ?></h1>
<?php echo get_option('contribution_consent_text'); ?>
</div>
<?php echo foot();
