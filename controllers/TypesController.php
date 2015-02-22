<?php 
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */
 
/**
 * Controller for editing and viewing Contribution plugin item types.
 */
class Contribution_TypesController extends Omeka_Controller_AbstractActionController
{
    public function init()
    {
        $this->_helper->db->setDefaultModelName('ContributionType');
    }
    
    public function addAction()
    {
        $typeRecord = new ContributionType();
        $this->view->action = 'add';
        $this->view->contribution_type = $typeRecord;
        $this->_processForm($typeRecord);
    }

    public function editAction()
    {
        $typeRecord = $this->_helper->db->findById();
        $this->view->action = 'edit';
        $this->view->contribution_type = $typeRecord;
        $this->_processForm($typeRecord);        
    }
    
    /**
     * Index action; simply forwards to browse.
     */
    public function indexAction()
    {
        $this->_redirect('contribution/types/browse');
    }
    
    public function showAction()
    {
        $this->_redirect('/');
    }

    public function addExistingElementAction()
    {
        if ($this->_getParam('from_post') == 'true') {
            $elementTempId = $this->_getParam('elementTempId');
            $elementId = $this->_getParam('elementId');
            $element = $this->_helper->db->getTable('Element')->find($elementId);
            if ($element) {
                $elementDescription = $element->description;
            }
            $elementOrder = $this->_getParam('elementOrder');
            $elementPromptValue = $element->prompt;
        } else {
            $elementTempId = '' . time();
            $elementId = '';
            $elementDescription = '';
            $elementOrder = intval($this->_getParam('elementCount')) + 1;
            $elementPromptValue = '';
        }
    
        $stem = Omeka_Form_ItemTypes::ELEMENTS_TO_ADD_INPUT_NAME . "[$elementTempId]";
        $elementIdName = $stem .'[id]';
        $elementOrderName = $stem .'[order]';
        $elementPromptName = $stem . '[prompt]';
        $elementLongName = $stem . '[long_text]';
        
        $item_type_id = $this->_getParam('itemTypeId');
        $this->view->assign(array('element_id_name' => $elementIdName,
                'element_id_value' => $elementId,
                'element_description' => $elementDescription,
                'element_order_name' => $elementOrderName,
                'element_order_value' => $elementOrder,
                'element_prompt_name' => $elementPromptName,
                'element_prompt_value' => $elementPromptValue,
                'element_long_name' => $elementLongName,
                'item_type_id' => $item_type_id 
        ));
    }
    
    public function changeExistingElementAction()
    {
        $elementId = $this->_getParam('elementId');
        $element = $this->_helper->db->getTable('Element')->find($elementId);
    
        $elementDescription = '';
        if ($element) {
            $elementDescription = $element->description;
        }
    
        $data = array();
        $data['elementDescription'] = $elementDescription;
    
        $this->_helper->json($data);
    }
    
    
    
    protected function  _getAddSuccessMessage($record)
    {
        return 'Type successfully added.';
    }

    protected function _getEditSuccessMessage($record)
    {
        return 'Type successfully updated.';
    }

    protected function _getDeleteSuccessMessage($record)
    {
        return 'Type deleted.';
    }
    
    private function _processForm($record)
    {
        $elementTable = $this->_helper->db->getTable('Element');
        $contributionElTable = $this->_helper->db->getTable('ContributionTypeElement');
        if ($this->getRequest()->isPost()) {
            try {
                $record->setPostData($_POST);
                if ($record->save()) {
                    if(isset($_POST['elements-to-add'])) {
                        foreach($_POST['elements-to-add'] as $tempId=>$elementInfo) {
                            if (empty($elementInfo['id'])) {
                                continue;
                            }
                            if(empty($elementInfo['prompt'])) {
                                $elementInfo['prompt'] = $elementTable->find($elementInfo['id'])->name;
                            }
                            $contributionEl = new ContributionTypeElement();
                            $contributionEl->element_id = $elementInfo['id'];
                            $contributionEl->prompt = $elementInfo['prompt'];
                            $contributionEl->order = $elementInfo['order'];
                            $contributionEl->long_text = $elementInfo['long_text'];
                            $contributionEl->type_id = $record->id;
                            $contributionEl->save();
                        }                        
                    }

                    $toRemove = isset($_POST['elements_to_remove']) ? explode(',', $_POST['elements_to_remove']) : array();
                    
                    foreach($_POST['elements'] as $id=>$elementInfo) {
                        if(!in_array($id, $toRemove)) {
                            $contributionEl = $contributionElTable->find($id);
                            if(empty($elementInfo['prompt'])) {
                                $elementInfo['prompt'] = $elementTable->find($contributionEl->element_id)->name;
                            }  
                            $contributionEl->prompt = $elementInfo['prompt'];
                            $contributionEl->order = $elementInfo['order'];                        
                            $contributionEl->long_text = $elementInfo['long_text'];
                            $contributionEl->save();
                        }
                    }
                    foreach($toRemove as $contribElId) {
                        $contribEl =$contributionElTable->find($contribElId);
                        if($contribEl) {
                            $contribEl->delete();    
                        } 
                    }
                    $this->_helper->redirector('browse');
                    return;
                }

            // Catch validation errors.
            } catch (Omeka_Validate_Exception $e) {
                $this->_helper->flashMessenger($e);
            }            
        }
    }
}
