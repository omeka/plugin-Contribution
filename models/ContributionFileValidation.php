<?php
/**
 * @version $Id$
 * @copyright Center for History and New Media, 2010
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package Contribution
 */

/**
 * Manipulates the set of file ingest validators that are active for the 
 * Contribution form.
 *
 * @package Contribution
 * @copyright Center for History and New Media, 2010
 */
class ContributionFileValidation
{
    const FILTER_NAME = 'file_ingest_validators';
        
    /**
     * TODO: Are there any validators in this set?
     * @var array Set of validators that will always apply to contributed files.
     */
    protected $_defaultValidators = array();
    
    protected $_validationMessages = array(
        'extension whitelist' => "The uploaded file (%value%) has a disallowed file extension.  Please modify the file format or upload a different file.",
        'MIME type whitelist' => "The uploaded file (%value%) has a disallowed media type.  Please modify the file format or upload a different file."
    );
        
    /**
     * Instantiate a validator.
     * 
     * @param array $info An array containing the shortened name of the 
     * validator class (name) and the options for the validator (options).
     * @return Zend_Validate_Abstract An instance of Zend_Validate_File_*
     **/    
    private function _getValidatorFromInfo($info)
    {
        $className = 'Zend_Validate_File_' . $info['name'];
        $validator = new $className($info['options']);
        $validator->setMessage($info['message']);
        return $validator;
    }
    
    /**
     * Enable the file validation filter for the Contribution form.
     * 
     * @return void
     **/
    public function enableFilter()
    {
        add_filter(self::FILTER_NAME, array($this, 'filterValidators'));
    }
    
    /**
     * Modify the validation error messages for the default file ingest 
     * validators.
     * 
     * Also add additional validators to the stack if necessary.
     * 
     * @param array
     * @return array
     **/
    public function filterValidators($validators)
    {        
        // Add the default validators.
        foreach ($this->_defaultValidators as $validatorInfo) {
            $validators[] = $this->_getValidatorFromInfo($validatorInfo);
        }
        
        // Reset the validation error messages to hide some info from public
        // site visitors.
        foreach ($this->_validationMessages as $validatorName => $validatorMsg) {
            if (array_key_exists($validatorName, $validators)) {
                $validators[$validatorName]->setMessage($validatorMsg);
            }
        }
        
        return $validators;
    }
}
