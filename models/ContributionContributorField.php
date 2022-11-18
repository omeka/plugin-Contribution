<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 * @subpackage Models
 */

/**
 * Record for contributor-specific questions.
 *
 * @package Contribution
 * @subpackage Models
 */
class ContributionContributorField extends Omeka_Record_AbstractRecord implements Zend_Acl_Resource_Interface
{
    public $prompt;
    public $type;
    public $order;

    /**
     * Validate form submissions.
     */
    protected function _validate()
    {
        if (empty($this->prompt)) {
            $this->addError('Question', 'Please provide a question.');
        }
        if ($this->prompt === 'Name' || $this->prompt === 'Email Address') {
            $this->addError('Question', 'The question "' . $this->prompt . '" is reserved. Please choose another.');
        }
        if (empty($this->type)) {
            $this->addError('Data Type', 'Please select a data type.');
        }
    }

    public function __toString()
    {
        $id = html_escape($this->id);
        $prompt = html_escape($this->prompt);
        $input = $this->getFormInput();

        return <<<HTML
<div class="field" id="contributor-field-$id">
    <label id="contributor-field-$id-label">$prompt</label>
    <div class="inputs">
        <div class="input">
            $input
        </div>
    </div>
</div>
HTML;
    }

    public function getResourceId()
    {
        return 'Contribution_ContributorField';
    }
    
    public function getRecordUrl($action = 'show')
    {
        $controller = str_replace('_', '-', Inflector::tableize('Contribution_ContributorMetadata'));
        return array('module' => 'contribution' , 'controller' => 'contributor-metadata', 'action' => $action, 'id' => $this->id);
    }
    
    private function getFormInput()
    {
        $inputName = "ContributorFields[{$this->id}]";
        if (isset($_POST['ContributorFields'])) {
            $defaultValue = $_POST['ContributorFields'][$this->id];
        } else {
            $defaultValue = '';
        }
        switch ($this->type) {
            case 'Tiny Text':
                $input = get_view()->formText($inputName, $defaultValue, array('class' => 'textinput', 'aria-labelledby' => 'contributor-field-' . $this->id . '-label'));
                break;
            case 'Text':
                $input = get_view()->formTextarea($inputName, $defaultValue, array('class' => 'textinput', 'rows' => 15));
                break;
        }
        return $input;
    }

    protected function beforeSave($args)
    {
        if (empty($this->order)) {
            $this->order = 0;
        }
    }
}
