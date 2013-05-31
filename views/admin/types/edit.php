<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */

$contributionTypeElements = $contribution_type->ContributionTypeElements;

$typeName = html_escape($contribution_type->display_name);
queue_css_file('contribution-type-form');

$addNewRequestUrl = admin_url('contribution/types/add-new-element');
$addExistingRequestUrl = admin_url('contribution/types/add-existing-element');
$changeExistingElementUrl = admin_url('contribution/types/change-existing-element'); 

queue_js_file('contribution-types');
$js = "
    jQuery(document).ready(function () {
        var addNewRequestUrl = '" . admin_url('contribution/types/add-new-element') . "'
        var addExistingRequestUrl = '" . admin_url('contribution/types/add-existing-element') . "'
        var changeExistingElementUrl = '" . admin_url('contribution/types/change-existing-element') . "'
        Omeka.ContributionTypes.manageContributionTypes(addNewRequestUrl, addExistingRequestUrl, changeExistingElementUrl);
        Omeka.ContributionTypes.enableSorting();
    });                
    ";
queue_js_string($js);
queue_css_file('contribution-type-form');
contribution_admin_header(array(__('Types'), __("Edit") . " &ldquo;$typeName&rdquo;"));
?>

<?php 
echo $this->partial('contribution-navigation.php');
?>

<div id="primary">
    <?php echo flash(); ?>
    <?php  include 'form.php'; ?>
</div>

<?php echo foot(); ?>
