<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */


queue_js_file('contribution');
queue_js_file('tiny_mce', 'javascripts/vendor/tiny_mce');
queue_js_string('setUpSettingsWysiwyg();');
contribution_admin_header(array(__('Submission Settings')));

?>

<?php 
echo $this->partial('contribution-navigation.php');
?>

<div id="primary">
    <?php echo flash(); ?>
    <?php echo $form; ?>
</div>

<?php echo foot(); ?>
