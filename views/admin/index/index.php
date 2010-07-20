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
              'bodyclass' => 'contribution primary',
              'content_class' => 'horizontal-nav');
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
<ul id="section-nav" class="navigation">
<?php echo nav(array('Start' => uri('contribution/index'), 'Settings' => uri('contribution/settings'), 'Types' => uri('contribution/types'))); ?>
</ul>
<div id="primary">
<?php echo flash(); ?>
</div>

<?php foot();