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
class ContributionContributorField extends Omeka_Record
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
    <label>$prompt</label>
    <div class="inputs">
        <div class="input">
            $input
        </div>
    </div>
</div>
HTML;
    }

    private function getFormInput()
    {
        $inputName = "ContributorFields[{$this->id}]";
        switch ($this->type) {
            case 'Tiny Text':
                $input = __v()->formText($inputName, $_POST[$inputName], array('class' => 'textinput'));
                break;
            case 'Text':
                $input = __v()->formTextarea($inputName, $_POST[$inputName], array('class' => 'textinput'));
                break;
        }
        return $input;
    }

    protected function  beforeSave() {
        if (empty($this->order)) {
            $this->order = 0;
        }
    }
}
