<?php
/**
 * @version $Id$
 * @author CHNM
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */

$head = array('title' => 'Contribute',
              'bodyClass' => 'contribution primary');
head(array('title' => $head['title'])); ?>
<?php echo js('jquery'); ?>
<script type="text/javascript">
jQuery.noConflict();

function displayTypeForm() {
    var form = jQuery('#contribution-type-form');
    var value = this.value;
    form.slideUp(400, function() { form.empty(); 
        if (value != "") {
            form.hide();
            jQuery.post('type-form', {typeId: value}, function(data) {
               form.append(data); 
               form.slideDown();
            });
        }
        });
}

jQuery(document).ready(function() {
   jQuery('#contribution-type').change(displayTypeForm);
});
</script>

<div id="primary">
    <h1><?php echo $head['title']; ?></h1>
    <form method="POST">
        <div class="inputs">
            <label for="contribution_type">What type of item do you want to contribute?</label>
            <?php echo contribution_select_type(array( 'name' => 'contribution-type', 'id' => 'contribution-type')); ?>
        </div>
        <div id="contribution-type-form">
        </div>
    </form>
</div>
<?php foot();