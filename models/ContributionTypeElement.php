<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 * @subpackage Models
 */

/**
 * An Element the user will be able to contribute for some type.
 *
 * @package Contribution
 * @subpackage Models
 */
class ContributionTypeElement extends Omeka_Record
{
    public $type_id;
    public $element_id;
    public $prompt;
    public $order;
    
    protected $_related = array('ContributionType' => 'getType',
                                'Element'          => 'getElement');

    protected function _validate()
    {
        if(empty($this->element_id)) {
            $this->addError('element', 'You must select an element to contribute.');
        }
        if(empty($this->prompt)) {
            $this->addError('prompt', 'You must provide a prompt for your contributors.');
        }
    }

    /**
     * Get the type associated with this type element.
     *
     * @return ContributionType
     */
    public function getType()
    {
        return $this->_db->getTable('ContributionType')->find($this->type_id);
    }
    
    /**
     * Get the Element associated with this type element.
     *
     * @return Element
     */
    public function getElement()
    {
        return $this->_db->getTable('Element')->find($this->element_id);
    }
}
