<?php
/**
 * Encapsulates the handling of installation/upgrade of the Contribution plugin.
 *
 * @package Contribution
 * @copyright Center for History and New Media, 2009
 **/
class Contribution_Upgrader
{    
    static public $contributionFormElementSetName = 'Contribution Form';
    
    static protected $_contributionElements = array(
         'Online Submission' => array(
            'name'=>'Online Submission',
            'description'=>'Indicates whether or not this Item has been contributed from a front-end contribution form.',
            'record_type'=>'Item'),
         'Posting Consent' => array(
             'name'=>'Posting Consent',
             'description'=>'Indicates whether or not the contributor of this Item has given permission to post this to the archive. (Yes/No)',
             'record_type'=>'Item'),
         'Submission Consent' => array(
             'name'=>'Submission Consent',
             'description'=>'Indicates whether or not the contributor of this Item has given permission to submit this to the archive. (Yes/No)',
             'record_type'=>'Item'),
         'Contributor is Creator' => array(
             'name'=>'Contributor is Creator',
             'description'=>'Indicates whether or not the contributor of the Item is responsible for its creation.',
             'record_type'=>'Item'
             ));
    
    /**
     * Install the Contribution plugin.
     * 
     * Create the 'Contribution Form' element set with 4 elements.  Create the
     * 'contributors' table in the database.  Set all the appropriate options
     * to the default values.
     * 
     * @return void
     **/
    public static function install()
    {
    	$elementSet = self::_insertContributionFormElementSet();
        self::_addElementsToElementSet(self::$_contributionElements, $elementSet);
        self::_addContributorsTable();
    	
    	set_option('contribution_plugin_version', CONTRIBUTION_PLUGIN_VERSION);
    	set_option('contribution_page_path', CONTRIBUTION_PAGE_PATH);
    	set_option('contribution_db_migration', CONTRIBUTION_MIGRATION);
    }
    
    /**
     * Upgrade the Contribution plugins from an existing version to another.
     * 
     * @param integer
     * @param integer
     * @return void
     **/
    public static function upgrade($from, $to)
    {
        $upgrader = new self;
        $wasUpgraded = false;
        
        $currentMigration = $from;
        while ($currentMigration < $to) {
            $migrateMethod = '_to' . ++$currentMigration;
            $upgrader->$migrateMethod();
            $wasUpgraded = true;
        }

        set_option('contribution_db_migration', $to);
        
        return $wasUpgraded;
    }
    
    /**
     * Upgrade the Contribution plugin database from its initial version.
     * 
     * In this case, '1' represents the first database migration and not the 
     * initial version of the plugin.  Performs the following tasks:
     * 
     * 1) Add the 'Contribution Form' element set.
     * 2) The existing elements would have fallen under the 'Additional Item
     * Metadata' element set, so if that set exists, then move all those elements
     * to the 'Contribution Form' set.
     * 3) Otherwise, add the elements as normal.
     * 
     * @return void
     **/
    private function _to1()
    {
        // Retrieve the existing elements and modify them to belong to the Contribution Form element set.
        $db = get_db();
        $sql  = "SELECT id FROM $db->ElementSet WHERE name = 'Additional Item Metadata'";
        $additionalItemElementSetId = $db->fetchOne($sql);

        // If the Additional Item Metadata element set does not exist for whatever
        // reason, there is no pre-existing plugin data to convert so just build
        // the whole thing from scratch.  Otherwise just make the new element set
        // w/o the new elements and convert the old ones.
        $contributionFormElementSet = self::_insertContributionFormElementSet();
        if (!$additionalItemElementSetId) {
            // Add the elements from scratch
            self::_addElementsToElementSet(self::$_contributionElements, $contributionFormElementSet);
        } else {
            // Otherwise, convert the existing elements.
            // Update the existing elements w/o interacting with the ElementSet models.
            $db->query(
        	            "UPDATE $db->Element SET element_set_id = ? 
        	            WHERE element_set_id = ? AND name IN (" . $db->quote(
        	                array('Online Submission', 'Posting Consent', 'Submission Consent')) .
        	            ") LIMIT 3",
        	            array($contributionFormElementSet->id, $additionalItemElementSetId));
        }
    }
    
    /**
     * The second database migration for the Contribution plugin.
     * 
     * This will add the 'Contribution is Creator' element and then populate it
     * with the 'Yes' flag for all the items that were submitted through the 
     * Contribution plugin that have the same 'Contributor' and 'Creator' field
     * values.
     * 
     * @return void
     **/
    private function _to2()
    {
        $this->_addContributorIsCreatorElement();
        $this->_fixItemsWhereContributorIsCreator();
    }
    
    /**
     * Add the 'Contributor is Creator' element to the Contribution Form 
     * element set.
     * 
     * If this element already exists, do nothing.
     * 
     * @return void
     **/
    private function _addContributorIsCreatorElement()
    {
        // Test to see whether or not we already have this element.
        $element = get_db()->getTable('Element')->findByElementSetNameAndElementName('Contribution Form', 'Contributor is Creator');
        if ($element) {
            return;
        }
        
        // Add the 'Contributor is Creator' element.
        $elementSet = $this->_getElementSet();
        self::_addElementsToElementSet(array(self::$_contributionElements['Contributor is Creator']), $elementSet);
    }
    
    /**
     * Update all the items that were previously submitted through the Contribution
     * plugin so that they contain the correct 'Contributor is Creator' flag.
     * 
     * TODO: Stress test this on large sets.
     * @return void
     **/
    private function _fixItemsWhereContributorIsCreator()
    {
        // Migrate existing data for contributions.
        // If the item is listed as a user contribution (Online Submission = 'Yes')
        // And Dublin Core Creator = Dublin Core Contributor
        // Then UPDATE 'Contributor is Creator' = 'Yes'.
        
        $elementTable = get_db()->getTable('Element');
        $onlineSubmissionElementId = $elementTable->findByElementSetNameAndElementName('Contribution Form', 'Online Submission')->id;
        
        // var_dump(compact('creatorElementId', 'contributorElementId', 'onlineSubmissionElementId', 'postingConsentElementId'));exit;
        $criteria = array(array(
                            'terms'=>'Yes',
                            'type'=>'contains',
                            'element_id'=>$onlineSubmissionElementId));
        
        $itemSelectSql = get_db()->getTable('Item')->getSelectForFindBy(array('advanced_search'=>$criteria));
        
        // SELECT only the record IDs for items that have the same value for 
        // Dublin Core Contributor and Creator.
        // 
        // How we do this: select from the set of all Dublin Core Creators,
        // inner join that against the set of all Dublin Core Contributors 
        // (using the record_id), and then a simple equivalence test between
        // the retrieved creator and contributor values.
        $itemSelectSql->where("i.id IN (
        SELECT creator_set.record_id FROM (
            SELECT etx.id, etx.record_id, etx.text as creator FROM element_texts etx
            INNER JOIN elements e ON e.id = etx.element_id    
            INNER JOIN element_sets es ON es.id = e.element_set_id
            WHERE e.name = 'Creator' AND es.name = 'Dublin Core'    
        ) creator_set
        INNER JOIN (
            SELECT etx.id, etx.record_id, etx.text as contributor
            FROM element_texts etx
            INNER JOIN elements e ON e.id = etx.element_id    
            INNER JOIN element_sets es ON es.id = e.element_set_id
            WHERE e.name = 'Contributor' AND es.name = 'Dublin Core'
        ) contributor_set ON contributor_set.record_id = creator_set.record_id
        WHERE creator = contributor        
        )"); 
        
        // Get the total # of items to change
                
        // FIXME: Chunk this data retrieval so it doesn't die when iterating through
        // potentially thousands of items.
        
        $itemsToFix = get_db()->getTable('Item')->fetchObjects($itemSelectSql);
        
        foreach ($itemsToFix as $key => $item) {
            update_item($item, array(), 
            array('Contribution Form'=>
                array('Contributor is Creator'=>
                    array(array('text'=>'Yes', 'html'=>false)))));
            release_object($item);
        }
    }
     
    /**
     * Create the 'contributors' table in the database.
     *
     * @return void
     **/
    private static function _addContributorsTable()
    {
        $db = get_db();
        $db->exec("CREATE TABLE IF NOT EXISTS `$db->Contributor` (
    			`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    			`entity_id` BIGINT UNSIGNED NOT NULL ,
    			`birth_year` YEAR NULL,
    			`gender` TINYTEXT NULL,
    			`race` TINYTEXT NULL,
    			`occupation` TINYTEXT NULL,
    			`zipcode` TINYTEXT NULL,
    			`ip_address` TINYTEXT NOT NULL
    			) ENGINE = MYISAM ;");
    }
    
    /**
     * Retrieve the 'Contribution Form' element set object.
     * 
     * @return ElementSet|null
     **/
    private function _getElementSet()
    {
        return get_db()->getTable('ElementSet')->findByName(self::$contributionFormElementSetName);
    }
    
    /**
     * Add the 'Contribution Form' element set to the database.
     * 
     * @return ElementSet
     **/    
    private static function _insertContributionFormElementSet()
    {
       $elementSet = get_db()->getTable('ElementSet')->findByName(self::$contributionFormElementSetName);
       if (!($elementSet)) {
           $elementSet = new ElementSet;
           $elementSet->name = self::$contributionFormElementSetName;
           $elementSet->description = "The set of elements containing metadata from the Contribution form.";
           
           // Die if this doesn't save properly.
           $elementSet->forceSave();
       }
       return $elementSet;
    }
    
    /**
     * Adds elements to an element set 
     *
     * @param array $elements The elements to add. 
     * @param ElementSet $elementSet 
     * @return void
     **/
    private static function _addElementsToElementSet($elements, $elementSet)
    {
        $oldElements = $elementSet->getElements();
        $newElements = array();
        foreach($elements as $element) {            
            $hasElement = false;
            foreach($oldElements as $oldElement) {
                if ($element['name'] == $oldElement->name) {
                    $hasElement = true;
                    break;
                }
            }
            if (!$hasElement) {
                $newElements[] = $element;
            } 
        }
        
        if (!empty($newElements)) {
            $elementSet->addElements($newElements);
            $elementSet->forceSave();
        }
    }
}