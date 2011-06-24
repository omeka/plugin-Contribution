<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */

require_once HELPER_DIR . DIRECTORY_SEPARATOR . 'ElementForm.php';

/**
 * Overrides Omeka's ElementForm helper to allow for custom display of fields.
 */
class Contribution_View_Helper_ElementForm extends Omeka_View_Helper_ElementForm
{
    protected $_contributionTypeElement;
    
    /**
     * Ideally, we should be able to call out to this function to display the
     * fields as the superclass does, but the "Add Input" button display is
     * hardcoded into the superclass' version, so we need to do this ugly
     * copy 'n paste.
     */
    public function elementForm(ContributionTypeElement $contributionTypeElement, 
                                Omeka_Record $record, $options = array())
    {
        $this->_contributionTypeElement = $contributionTypeElement;
        $element = $contributionTypeElement->getElement();

        // Skip this form if the element no longer exists.
        if (!$element) {
            return;
        }
        
        $divWrap = isset($options['divWrap']) ? $options['divWrap'] : true;
        $extraFieldCount = 0;
        
        $this->_element = $element;
        
        // This will load all the Elements available for the record and fatal error
        // if $record does not use the ActsAsElementText mixin.
        $record->loadElementsAndTexts();
        $this->_record = $record;
        
        $html = $divWrap ? '<div class="field" id="element-' . html_escape($element->id) . '">' : '';
        
        // Put out the label for the field
        $html .= $this->_displayFieldLabel();
        
        $html .= $this->_displayValidationErrors();
        
        $html .= '<div class="inputs">';
        $html .= $this->_displayFormFields($extraFieldCount);
        $html .= '</div>'; // Close 'inputs' div
        
        $html .= $divWrap ? '</div>' : ''; // Close 'field' div
        
        return $html;
    }
    
    /**
     * Uses the type's alias to display rather than the element name.
     */
    protected function _getFieldLabel()
    {
        return html_escape($this->_contributionTypeElement->prompt);
    }
    
    /**
     * Removes "Remove input" button from element output
     */
    protected function _displayFormControls()
    {}
    
    /**
     * Removes "Use HTML" checkbox from element output
     */
    protected function _displayHtmlFlag($inputNameStem, $index)
    {}
}
