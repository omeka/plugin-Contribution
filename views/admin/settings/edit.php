<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */

contribution_admin_header(array('Submission Settings'));
?>
<div id="primary">
    <?php echo flash(); ?>
    <?php echo $form; ?>
</div>
<?php

//queue_js_file('tiny_mce/tiny_mce');
queue_js_file('contribution');

?>
<script type="text/javascript">
// <![CDATA[
    setUpSettingsWysiwyg();
// ]]>
</script>
<?php echo foot();
