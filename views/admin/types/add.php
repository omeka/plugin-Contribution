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
$elements = $itemType->Elements;
contribution_admin_header(array('Types', 'Add a New Type'));
?>

<?php 
echo $this->partial('contribution-navigation.php');
?>

<div id="primary">
    <?php echo flash(); ?>
    <form method='post'>
    <?php echo $this->form; ?>
    <div style='clear:both'></div>
    <?php include 'elements-form.php'; ?>
    </section>
    </form>
    
</div>
<?php echo foot(); ?>