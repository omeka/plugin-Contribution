<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */

$contributionTypeElements = $contribution_type->ContributionTypeElements;
debug('edit page');
$itemType = $contribution_type->ItemType;
$elements = $itemType->Elements;

$typeName = html_escape($contribution_type->display_name);
queue_css_file('contribution-type-form');
queue_js_file('contribution');

//$promptInput = $this->formText('newElements[!!INDEX!!][prompt]', null, array('class' => 'textinput element-prompt'));
//$elementSelect = contribution_select_element_for_type($contribution_type, 'newElements[!!INDEX!!][element_id]');
$addNewRequestUrl = admin_url('contribution/types/add-new-element');
$addExistingRequestUrl = admin_url('contribution/types/add-existing-element');
$changeExistingElementUrl = admin_url('contribution/types/change-existing-element'); 

$js = "
    var newRow = '<li>' . $promptInput . ' ' . $elementSelect . '</li>';
    setUpTableSorting('#elements', '.element-order');
    setUpTableAppend('#add-element', '#elements', newRow);                

    
                ";

//queue_js_string($js);
queue_js_file('contribution-types');

contribution_admin_header(array('Types', "Edit &ldquo;$typeName&rdquo;"));
?>
<?php //echo delete_button(null, 'delete-type', 'Delete this Type', array(), 'delete-record-form'); ?>


<?php 
echo $this->partial('contribution-navigation.php');
?>

<div id="primary">
    <?php echo flash(); ?>
    <form method='post'>
    <?php echo $this->form; ?>
    
    <?php  include 'elements-form.php'; ?>
    </section>
    </form>
    
</div>

<?php echo foot(); ?>
