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
    public $name;
    public $prompt;
    public $type;
    public $order;

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
                $input = __v()->formText($inputName, null, array('class' => 'textinput'));
                break;
            case 'Text':
                $input = __v()->formTextarea($inputName, null, array('class' => 'textinput'));
                break;
        }
        return $input;
    }
}
