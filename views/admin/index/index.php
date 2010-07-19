<?php
/**
 * @version $Id$
 * @author CHNM
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */

$h1 = 'Contribution';
$head = array('title' => $h1,
              'bodyclass' => 'contribution primary');
head($head);
echo js('jquery'); ?>
<script type="text/javascript">
jQuery.noConflict();
</script>
<?php
echo js('tiny_mce/tiny_mce');
echo js('contribution-settings-tinymce');
?>
<h1><?php echo $h1; ?></h1>
<div id="primary">
<?php echo flash(); ?>
    <p><a href="<?php echo uri('contribution/settings'); ?>">Settings</a></p>
    <p><a href="<?php echo uri('contribution/types'); ?>">Types</a></p>
</div>

<?php foot();