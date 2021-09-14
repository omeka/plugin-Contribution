<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */
$id = html_escape($contribution_contributor->id);
$customMetadata = $contribution_contributor->getMetadata();

contribution_admin_header(array('Contributors', "#$id"));
?>

<?php 
echo $this->partial('contribution-navigation.php');
?>

<div id="primary">
    <?php echo flash(); ?>
    <h2>Basic Metadata</h2>
    <div class="table-responsive">
    <table>
        <tr>
            <th>Name</th>
            <td><?php echo html_escape($contribution_contributor->name); ?></td>
        </tr>
        <tr>
            <th>Email Address</th>
            <td><?php echo html_escape($contribution_contributor->email); ?></td>
        </tr>
        <tr>
            <th>IP Address</th>
            <td><?php echo $contribution_contributor->getDottedIpAddress(); ?></td>
        </tr>
    </table>
    </div>
    <h2>Custom Metadata</h2>
    <div class="table-responsive">
    <table>
        <?php foreach ($customMetadata as $metadataName => $metadataValue): ?>
        <tr>
            <th><?php echo html_escape($metadataName); ?></th>
            <td><?php echo html_escape($metadataValue); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    </div>
</div>
<?php echo foot(); ?>