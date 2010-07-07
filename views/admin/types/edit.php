<?php
/**
 * @version $Id$
 * @author CHNM
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */

function display_file_upload($fileAllowed, $fileRequired)
{
    if($fileAllowed) {
        if($fileRequired) {
            return 'Required';
        }
        return 'Allowed';
    }
    return 'Not Allowed';
}

$h1 = 'Contribution';
$h2 = "Type &ldquo;$contributionType->alias&rdquo";
$head = array('title' => "$h1 | $h2",
              'bodyClass' => 'contribution primary');
head(array('title' => $head['title']));
?>

<h1><a href="<?php echo uri('contribution'); ?>"><?php echo $h1; ?></a> | <?php echo $h2; ?></h1>
<div id="primary">
    <?php echo flash(); ?>
<?php foreach ($elements as $element): ?>
    <p><?php echo $element->name; ?></p>
<?php endforeach; ?>
</div>

<?php foot();