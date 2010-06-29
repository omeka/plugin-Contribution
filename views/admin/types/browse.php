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
$head = array('title' => "$h1 | $h2",
              'bodyClass' => 'contribution primary');
head(array('title' => $head['title']));
?>

<div id="primary">
<?php echo flash(); ?>
    <h1><a href="<?php echo uri('contribution') ?>"><?php echo $h1; ?></a> | <?php echo $h2; ?></h1>
    <table>
        <thead>
            <tr>
                <th>Display Name</th>
                <th>Item Type</th>
                <th>File Upload</th>
            </tr>
        </thead>
        <tbody>
<?php foreach ($typeInfoArray as $typeInfo): ?>
            <tr>
                <td><?php echo $typeInfo['alias']; ?></td>
                <td><a href="<?php echo uri('item-types/show/'.$typeInfo['item_type_id']); ?>"><?php echo $typeInfo['item_type_name']; ?></a></td>
                <td><?php echo display_file_upload($typeInfo['file_allowed'], $typeInfo['file_required']); ?></td>
            </tr>
<?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php foot();