<?php
/**
 * @version $Id$
 * @author CHNM
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */

$head = array('title' => 'Contribution Settings',
              'bodyClass' => 'contribution primary');
head(array('title' => $head['title'])); ?>

<div id="primary">
<?php echo flash(); ?>
    <h1><?php echo $head['title']; ?></h1>
<?php echo $form; ?>
</div>

<?php foot();