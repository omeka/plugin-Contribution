<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */

if (version_compare(OMEKA_VERSION, '2.0-dev', '<')) {
    require_once VIEW_HELPERS_DIR . DIRECTORY_SEPARATOR . 'ElementForm.php';
} else {
    require_once VIEW_HELPERS_DIR . DIRECTORY_SEPARATOR . 'ElementForm.php';
}

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
                                Omeka_Record_AbstractRecord $record, $options = array())
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
        $html .= $this->_getValueForField($element->id);
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
    
    protected function _displayFieldLabel()
    {
        return '<label>'.__($this->_getFieldLabel()).'</label>';
    }
    
    protected function _displayValidationErrors()
    {
        flash($this->_contributionTypeElement->prompt);
    }
    
    protected function _displayFormFields($extraFieldCount = null)
    {
           $fieldCount = $this->_getFormFieldCount() + (int) $extraFieldCount;

        $html = '';

        for ($i=0; $i < $fieldCount; $i++) {
            $html .= '<div class="input-block">';

            $fieldStem = $this->_getFieldNameStem($i);

            $html .= '<div class="input">';
            $html .= $this->_displayFormInput($fieldStem, $this->_getValueForField($i));
            $html .= '</div>';

            $html .= $this->_displayFormControls();

            $html .= $this->_displayHtmlFlag($fieldStem, $i);

            $html .= '</div>';
        }

        return $html;    
    }
    
    protected function _getFieldNameStem($index)
    {
        return "Elements[".$this->_contributionTypeElement['id']."][$index]";
    }
   protected function _displayFormInput($inputNameStem, $value, $options=array())
    {
        $fieldDataType = $this->_getElementDataType();

        // Plugins should apply a filter to this blank HTML in order to display it in a certain way.
        $html = '';

        $filterName = $this->_getPluginFilterForFormInput();

        //$html = apply_filters($filterName, $html, $inputNameStem, $value, $options, $this->_record, $this->_element);
        $html = apply_filters($filterName, $html, array('view'=>$this));

        // Short-circuit the default display functions b/c we already have the HTML we need.
        if (!empty($html)) {
            return $html;
        }

        //Create a form input based on the element type name
        switch ($fieldDataType) {

            //Tiny Text => input type="text"
            case 'Tiny Text':
                return $this->view->formTextarea(
                    $inputNameStem . '[text]',
                    $value,
                    array('class'=>'textinput', 'rows'=>2, 'cols'=>50));
                break;
            //Text => textarea
            case 'Text':
                return $this->view->formTextarea(
                    $inputNameStem . '[text]',
                    $value,
                    array('class'=>'textinput', 'rows'=>15, 'cols'=>50));
                break;
            case 'Date':
                return $this->_dateField(
                    $inputNameStem,
                    $value,
                    array());
                break;
            case 'Date Range':
                return $this->_dateRangeField(
                    $inputNameStem,
                    $value,
                    array());
            case 'Integer':
                return $this->view->formText(
                    $inputNameStem . '[text]',
                    $value,
                    array('class' => 'textinput', 'size' => 40));
            case 'Date Time':
                return $this->_dateTimeField(
                    $inputNameStem,
                    $value,
                    array());
            default:
                throw new Exception(__('Cannot display a form input for "%s" if element type name is not given!', $element['name']));
                break;
        }

    }
    
    protected function _getElementDataType()
    {
        return $this->_contributionTypeElement['data_type_name'];
    }
    protected function _getPluginFilterForFormInput()
    {
        return array(
            'Form',
            get_class($this->_record),
            $this->_element->set_name,
            $this->_element->name
        );
    }
}
