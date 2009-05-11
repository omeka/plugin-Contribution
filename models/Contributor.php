<?php
require_once 'ContributorTable.php';
/**
 * Contributor
 * @package: Omeka
 */
class Contributor extends Omeka_Record
{
    public $entity_id;
	public $birth_year = '0000';
	public $race = '';
	public $gender = '';
	public $occupation = '';
	public $zipcode = '';
	public $ip_address;
	
	protected $_related = array('Entity'=>'getEntity');
	
	public function __get($property)
	{
	    switch ($property) {
	       case 'name':
	           return $this->Entity->first_name . ' ' . $this->Entity->last_name;
	           break;
	       case 'email':
	           return $this->Entity->email;
	           break;
	       default:
	           return parent::__get($property);
	           break;
	    }
	}
	
	/**
	 * Use ZF's Zend_Filter_Input mechanism to properly clean all the user input
	 *
	 * @return void
	 **/
	protected function filterInput($input)
	{
		$options = array('namespace'=>'Omeka_Filter');
		
		unset($input['entity_id']);
		unset($input['ip_address']);
		
		$filters = array(
			'*' 		=> 'StringTrim',
			'entity_id' => 'ForeignKey');
			
		$filter = new Zend_Filter_Input($filters, null, $input, $options);
		
		return $filter->getUnescaped();
	}

	//Gotta have a valid 1) ip address, 2) email address, 3) first & last name
	protected function _validate()
	{		
		if(empty($this->ip_address)) {
			$this->addError('ip_address', 'Contributors must come from a valid IP address.');
		}
		
		if(!Zend_Validate::is($this->email, 'EmailAddress')) {
			$this->addError('email', 'The email address you have provided is invalid.  Please provide another one.');
		}
		
		if(empty($this->first_name) or empty($this->last_name)) {
			$this->addError('name', 'The first/last name fields must be filled out.  Please provide a complete name.');		
		}
	}
	
	//Retrieve the entity associated with this contributor
	public function getEntity()
	{
		return $this->getTable('Entity')->find((int) $this->entity_id);
	}
	
	//If the contributor is a new entry, then pull in the IP address of the browser before saving
	protected function beforeValidate()
	{
		if(empty($this->ip_address) and !$this->exists()) {
			$this->ip_address = $_SERVER['REMOTE_ADDR'];
		}
	}
	
	//Save the entity or die, then hook it up to the contributor
	protected function beforeInsert()
	{
		$this->Entity->forceSave();
		$this->entity_id = $this->Entity->id;
	}
	
	//Delete the entity associated with this Contributor
	protected function beforeDelete()
	{
		$this->Entity->delete();
	}
	
	//Create the entity that the Contributor will be linked to, but don't save it just yet
	public function createEntity(array $input)
	{
		require_once 'Entity.php';
		$entity = new Entity;
		$entity->setArray($input);

		$this->Entity = $entity;
	}
	
	/**
	 * Delete the Contributor, optionally delete items associated with the
	 * Contributor.
	 * 
	 * @param boolean $deleteItems Whether or not to delete items that were added
	 * by this contributor.
	 * @return boolean
	 **/
	public function delete($deleteItems = false)
	{
	    if ($deleteItems) {
	       // Find all the items associated with this contributor.
	       $contributedItems = $this->getTable('Item')->findBy(array(
	           'contributor'=>$this->id));
	       
	       foreach ($contributedItems as $item) {
	           $item->delete();
	       }
	    }
	    	    
	    return parent::delete();
	}
}