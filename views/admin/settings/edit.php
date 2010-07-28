<?php
/**
 * @version $Id$
 * @author CHNM
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */

contribution_admin_header(array('Settings'));
?>
<div id="primary">
    <?php echo flash(); ?>
    <?php echo $form; ?>
</div>
<?php echo js('jquery'); ?>
<script type="text/javascript">
jQuery.noConflict();
</script>
<?php
echo js('tiny_mce/tiny_mce');
echo js('contribution-settings-tinymce');
?>
<?php foot();