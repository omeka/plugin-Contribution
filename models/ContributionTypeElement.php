<?php
/**
 * @version $Id$
 * @author CHNM
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */

class ContributionTypeElement extends Omeka_Record
{
	public $type_id;
	public $alias;
	
	protected $_related = array('ContributionType' => 'getType');
	
	protected function initializeMixins()
	{
		$this->mixins[] = new Relatable($this);
	}
	
	/**
	 * Get the type associated with this type element.
	 * @todo actually implement
	 */
	public function getType()
	{
		
	}
}