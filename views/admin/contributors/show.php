<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */
$contributor = $contributioncontributor;
$displayData = array_map('html_escape', (array)$contributor);

$customMetadata = $contributioncontributor->getMetadata();

contribution_admin_header(array('Contributors', "#{$displayData['id']}"));
?>
<div id="primary">
    <?php echo flash(); ?>
    <h2>Basic Metadata</h2>
    <table>
        <tr>
            <th>Name</th>
            <td><?php echo $displayData['name']; ?></td>
        </tr>
        <tr>
            <th>Email Address</th>
            <td><?php echo $displayData['email']; ?></td>
        </tr>
        <tr>
            <th>IP Address</th>
            <td><?php echo $contributor->getDottedIpAddress(); ?></td>
        </tr>
    </table>
    <h2>Custom Metadata</h2>
    <table>
        <?php foreach ($customMetadata as $metadataName => $metadataValue): ?>
        <tr>
            <th><?php echo html_escape($metadataName); ?></th>
            <td><?php echo html_escape($metadataValue); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php foot();