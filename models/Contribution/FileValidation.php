<?php
/**
 * @version $Id$
 * @copyright Center for History and New Media, 2009
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package Contribution
 **/

/**
 * Assign the correct validators for file ingest based on the type of 
 * Contribution that is being made.
 * 
 * Stores validator info for the various item types and interacts with the system
 * through the file_ingest_validators' filter.
 *
 * @package Contribution
 * @copyright Center for History and New Media, 2009
 **/
class Contribution_FileValidation
{
    const FILTER_NAME = 'file_ingest_validators';
    
    /**
     * @var string Name of the Item Type to which the contributed item belongs.
     */
    protected $_itemTypeName;
    
    /**
     * @var array Keyed to the name of the Item Type, contains a list of 
     * validators to use.
     */
    protected $_itemTypeValidators = array(
        'Still Image' => array(
            array('name'    => 'MimeType', 
                  'options' => 'image', // All image media types.
                  'message' => "The type of file uploaded (%type%) is not recognized as a valid image.  Please change the file format or upload a different image.")),
        'Moving Image' => array(
            array('name'    => 'MimeType', 
                  'options' => 'video',
                  'message' => 'The type of file uploaded (%type%) is not recognized as a valid movie.  Please change the file format or upload a different movie.')),
        'Sound' => array(
            array('name'    => 'MimeType', 
                  'options' => 'audio',
                  'message' => 'The type of file uploaded (%type%) is not recognized as a valid audio file.  Please change the format or upload a different file.'))
    );
    
    /**
     * TODO: Are there any validators in this set?
     * @var array Set of validators that will always apply to contributed files.
     */
    protected $_defaultValidators = array();
    
    /**
     * Constructor.
     * 
     * @param string
     * @return void
     **/
    public function __construct($itemTypeName)
    {
        $this->_itemTypeName = $itemTypeName;
    }
    
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
     * Filter the set of validators so that it only contains the proper
     * whitelists for the given Item Type that is being contributed.
     * 
     * Dump the existing set in favor of the new set.
     * 
     * @param array
     * @return array
     **/
    public function filterValidators($validators)
    {        
        $validators = array();
        // Add the default validators.
        foreach ($this->_defaultValidators as $validatorInfo) {
            $validators[] = $this->_getValidatorFromInfo($validatorInfo);
        }
        
        // Add the Item Type validators.
        $itemTypeValidatorInfo = $this->_itemTypeValidators[$this->_itemTypeName];
        
        if (!$itemTypeValidatorInfo) {
            throw new Contribution_Exception("Invalid Item Type provided!");
        }
        
        foreach ($itemTypeValidatorInfo as $validatorInfo) {
            $validators[] = $this->_getValidatorFromInfo($validatorInfo);
        }
        return $validators;
    }
}
