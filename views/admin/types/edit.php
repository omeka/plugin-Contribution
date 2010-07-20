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
$h2 = 'Types';
$h3 = "&ldquo;$contributionType->display_name&rdquo;";
$head = array('title' => "$h1 | $h2 | $h3",
              'bodyclass' => 'contribution primary');
head($head);
?>

<h1><a href="<?php echo uri('contribution'); ?>"><?php echo $h1; ?></a> | <a href="<?php echo uri('contribution/types'); ?>"><?php echo $h2; ?></a> | <?php echo $h3; ?></h1>
<div id="primary">
    <?php echo flash(); ?>
<h2>Type Description</h2>
<p><?php echo html_escape($itemType->description); ?>

<h2>Type Elements</h2>
<form method="POST">
    <table>
        <thead>
            <tr>
                <th></th>
                <th>Element Name</th>
                <th>Description</th>
                <th>Prompt</th>
            </tr>
        </thead>
        <tbody>
<?php foreach ($contributionTypeElements as $element): 
$id = $element->id; ?>
            <tr>
                <td><?php echo $this->formCheckbox("Elements[$id][enabled]", null, array('checked' => true))?></td>
                <td><?php echo html_escape($element->Element->name); ?></td>
                <td><?php echo html_escape($element->Element->description); ?></td>
                <td><?php echo $this->formTextarea("Elements[$id][prompt]", $element->prompt, array('rows' => 2, 'cols' => 40)); ?></td>
            </tr>
<?php endforeach; ?>
        </tbody>
    </table>
</form>
</div>

<?php foot();