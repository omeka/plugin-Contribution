<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */

$contributionType = $contribution_type;
$contributionTypeElements = $contribution_type->ContributionTypeElements;
$itemType = $contribution_type->ItemType;
if($itemType) {
    $elements = $itemType->Elements;    
} else {
    $elements = array();
}

contribution_admin_header(array('Types', 'Add a New Type'));
?>

<?php 
echo $this->partial('contribution-navigation.php');
?>

<div id="primary">
    <?php echo flash(); ?>
    <?php include 'form.php'; ?>
</div>
<?php echo foot(); ?>
