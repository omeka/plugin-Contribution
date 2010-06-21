<?php
/**
 * @version $Id$
 * @author CHNM
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */

/**
 * Overrides Omeka's ElementForm helper to allow for custom display of fields.
 */
class Contribution_View_Helper_ElementForm extends Omeka_View_Helper_ElementForm
{
    protected $_contributionTypeElement;
    
    public function elementForm(ContributionTypeElement $contributionTypeElement, 
                                Omeka_Record $record, $options = array())
    {
        $this->_contributionTypeElement = $contributionTypeElement;
        $element = $contributionTypeElement->getElement();
        
        parent::elementForm($element, $record, $options);
    }
    
    /**
     * Uses the type's alias to display rather than the element name.
     */
	protected function _getFieldLabel()
	{
		return html_escape($this->_contributionTypeElement->alias);
	}
}