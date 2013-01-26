<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */
$id = html_escape($contributor->id);

contribution_admin_header(array('Contributors', "#$id"));
?>


<?php 
echo $this->partial('contribution-navigation.php');
?>

<div id="primary">
    <?php echo flash(); ?>
    <h2><?php echo $contributor->name; ?>'s contributions</h2>

    <div id='contribution-basic-user-info'>
    <p>Email: <?php echo metadata($contributor, 'email'); ?></p>
    </div>
    
    <div id='contribution-profile-info'>
    
    <p>If User Profiles installed and setup for Contribution, that user profile info here</p>
    
    </div>
    
    <div id='contribution-user-contributions'>
        <?php foreach($items as $item): ?>
        <?php set_current_record('item', $item->Item); ?>
        <div>
        <?php echo link_to_item(); ?>
        <?php echo metadata('item', 'added'); ?>
        <?php echo files_for_item();?>
        </div>        
        
        <?php endforeach; ?>

    </div>
</div>
<?php echo foot(); ?>
