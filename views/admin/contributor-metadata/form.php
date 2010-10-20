<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */
$field = $contributioncontributorfield;
?>
    <form method="post" action="">
        <div class="field">
            <?php echo $this->formLabel('prompt', 'Question'); ?>
            <div class="inputs">
                <?php echo $this->formText('prompt', $field->prompt, array('class' => 'textinput', 'size' => '60')); ?>
                <p class="explanation">The text of the question to ask contributors.</p>
            </div>
        </div>
        <div class="field">
            <?php echo $this->formLabel('type', 'Select Size of Field'); ?>
            <div class="inputs">
                <?php echo contribution_select_field_data_type('type', $field->type); ?>
                <p class="explanation">Choose text box size given to users for answering this question.</p>
            </div>
        </div>
        <?php echo $this->formSubmit('submit-changes', 'Submit Changes', array('class' => 'submit-button')); ?>
    </form>
