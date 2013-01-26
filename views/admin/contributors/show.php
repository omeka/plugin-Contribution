<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */
$contributor = $contributioncontributor;
$id = html_escape($contributor->id);
$customMetadata = $contributioncontributor->getMetadata();

contribution_admin_header(array('Contributors', "#$id"));
?>


<?php 
echo $this->partial('contribution-navigation.php');
?>

<div id="primary">
    <?php echo flash(); ?>
    <h2>Basic Metadata</h2>

    <div id='contribution-basic-user-info'>
    <p>Basic user info</p>
    </div>
    
    <div id='contribution-profile-info'>
    
    <p>If User Profiles installed and setup for Contribution, that user profile info here</p>
    
    </div>
    
    <div id='contribution-user-contributions'>
    <p>List of user's contributions here</p>
    </div>
</div>
<?php echo foot(); ?>
