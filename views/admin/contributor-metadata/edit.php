<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */

contribution_admin_header(array('Contributor Metadata', 'Edit'));
$field = $contributioncontributorfield;
?>
<div id="primary">
    <?php echo flash(); ?>
    <form method="post">
        <div class="field">
            <?php echo $this->formLabel('name', 'Name'); ?>
            <div class="input">
                <?php echo $this->formText('name', $field->name, array('class' => 'textinput')); ?>
                <p class="explanation">Used to identify this field on the admin interface.</p>
            </div>
        </div>
        <div class="field">
            <?php echo $this->formLabel('prompt', 'Prompt'); ?>
            <div class="input">
                <?php echo $this->formText('prompt', $field->prompt, array('class' => 'textinput')); ?>
                <p class="explanation">The name that is printed on the public form.</p>
            </div>
        </div>
        <div class="field">
            <?php echo $this->formLabel('type', 'Data Type'); ?>
            <div class="input">
                <?php echo contribution_select_field_data_type('type', $field->type); ?>
                <p class="explanation">The type of data that will be submitted by users answering this question.</p>
            </div>
        </div>
        <?php echo $this->formSubmit('submit-changes', 'Submit Changes', array('class' => 'submit-button')); ?>
    </form>
    <?php echo delete_button(); ?>
</div>
<?php foot();