<?php
/**
 * @version $Id$
 * @author CHNM
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */

$h1 = 'Contribution';
$h2 = 'Settings';
$head = array('title' => "$h1 | $h2",
              'bodyClass' => 'contribution primary');
head(array('title' => $head['title']));
echo js('jquery'); ?>
<script type="text/javascript">
jQuery.noConflict();
</script>
<?php
echo js('tiny_mce/tiny_mce');
echo js('contribution-settings-tinymce');
?>
<h1><a href="<?php echo uri('contribution'); ?>"><?php echo $h1; ?></a> | <?php echo $h2; ?></h1>
<div id="primary">
<?php echo flash(); ?>
<?php echo $form; ?>
</div>

<?php foot();