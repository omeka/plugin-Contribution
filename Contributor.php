<?php
/**
 * Contributor
 * @package: Omeka
 */
class Contributor extends Kea_Record
{
    public function setTableDefinition()
    {
		$this->setTableName('contributors');
		
		$this->hasColumn('entity_id', 'integer', null, array('range'=>array('1')));
		$this->hasColumn('birth_year', 'integer', 5);
		$this->hasColumn('race', 'string', 255);
		$this->hasColumn('gender', 'string', 255);
		$this->hasColumn('occupation', 'string', 255);
		$this->hasColumn('zipcode', 'string', 50);
		$this->hasColumn('ip_address', 'string', 40, array('notblank'=>true));
    }
    public function setUp()
    {
		$this->hasOne("Entity", "Contributor.entity_id");
    }
}

?>