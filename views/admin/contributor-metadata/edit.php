<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */

contribution_admin_header(array('Contributor Questions', 'Edit'));
?>
<?php echo delete_button(null, 'delete-question', 'Delete this Question', array(), 'delete-record-form'); ?>
<div id="primary">
    <?php echo flash(); ?>
    <?php require 'form.php'; ?>
</div>
<?php foot();
