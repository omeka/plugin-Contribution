<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */


$prompt = isset($contribution_contributor_field->prompt) ? $contribution_contributor_field->prompt : '';
$type = isset($contribution_contributor_field->type) ? $contribution_contributor_field->type : ''; 

?>
    <form method="post" action="">
        <section class="seven columns alpha">
            <div class="field">
                <?php echo $this->formLabel('prompt', 'Question'); ?>
                <div class="inputs">
                    <?php echo $this->formText('prompt', $prompt, array('class' => 'textinput', 'size' => '60')); ?>
                    <p class="explanation">The text of the question to ask contributors.</p>
                </div>
            </div>
            <div class="field">
                <?php echo $this->formLabel('type', 'Select Size of Field'); ?>
                <div class="inputs">
                    <?php echo contribution_select_field_data_type('type', $type); ?>
                    <p class="explanation">Choose text box size given to users for answering this question.</p>
                </div>
            </div>
        </section>
        <section class="three columns omega">
            <div id="save" class="panel">
                <input type="submit" class="big green button" value="<?php echo __('Save Changes');?>" id="submit" name="submit">
                <?php echo link_to($contribution_contributor_field, 'delete-confirm', __('Delete'), array('class' => 'big red button delete-confirm')); ?>
            </div>
        </section>
    </form>
    <div class="clearfix"></div>

