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
        $form = $this->_getForm($typeRecord, 'add');
        $this->view->form = $form;
        $this->view->contribution_type = $typeRecord;
        $this->_processForm($typeRecord);
    }

    public function editAction()
    {
        $typeRecord = $this->_helper->db->findById();
        $form = $this->_getForm($typeRecord, 'edit');
        
        $this->view->form = $form;
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

    
    public function addNewElementAction()
    {
        if ($this->_getParam('from_post') == 'true') {
            $elementTempId = $this->_getParam('elementTempId');
            $elementName = $this->_getParam('elementName');
            $elementDescription = $this->_getParam('elementDescription');
            $elementOrder = $this->_getParam('elementOrder');
        } else {
            $elementTempId = '' . time();
            $elementName = '';
            $elementDescription = '';
            $elementOrder = intval($this->_getParam('elementCount')) + 1;
        }
    
        $stem = Omeka_Form_ItemTypes::NEW_ELEMENTS_INPUT_NAME . "[$elementTempId]";
        $elementNameName = $stem . '[name]';
        $elementDescriptionName = $stem . '[description]';
        $elementOrderName = $stem . '[order]';
        $item_type_id = $this->_getParam('itemTypeId');    
        $this->view->assign(array('element_name_name' => $elementNameName,
                'element_name_value' => $elementName,
                'element_description_name' => $elementDescriptionName,
                'element_description_value' => $elementDescription,
                'element_order_name' => $elementOrderName,
                'element_order_value' => $elementOrder,
                'item_type_id' => $item_type_id
        ));
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
        
        $item_type_id = $this->_getParam('itemTypeId');
        $this->view->assign(array('element_id_name' => $elementIdName,
                'element_id_value' => $elementId,
                'element_description' => $elementDescription,
                'element_order_name' => $elementOrderName,
                'element_order_value' => $elementOrder,
                'element_prompt_name' => $elementPromptName,
                'element_prompt_value' => $elementPromptValue,
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
    
    protected function _getForm($typeRecord, $action)
    {
        $itemTypeOptions = get_db()->getTable('ContributionType')->getPossibleItemTypes();
        $itemTypeOptions = array('' => 'Select an Item Type') + $itemTypeOptions;
        
        $form = "<section class='seven columns alpha'>";
        if($action == 'add') {
            $form .= '<div class="field">
                    <div class="two columns alpha">
                        <label>Item Type</label>
                    </div>
                    <div class="inputs five columns omega">
                        <p class="explanation">The Item Type, from your site\'s list of types, you would like to use.</p>
                        <div class="input-block">
                           ' . $this->view->formSelect('item_type_id', $typeRecord->item_type_id, array(), $itemTypeOptions) . '
                        </div>
                    </div>
                 </div>';
        } else {
            $form .= '<input type="hidden" id="item_type_id" value="' . $typeRecord->item_type_id . '"/>';
        }
        
        $form .= '<div class="field">
                    <div class="two columns alpha">
                        <label>Display Name</label>
                    </div>
                    <div class="inputs five columns omega">
                        <p class="explanation">The label you would like to use for this contribution type. If blank, the Item Type name will be used.</p>
                        <div class="input-block">
                         ' .   $this->view->formText('display_name', $typeRecord->display_name, array()) . '
                        </div>
                    </div>
                 </div>';
        
        $form .= '<div class="field">
                    <div class="two columns alpha">
                        <label>Allow File Upload Via Form</label>
                    </div>
                    <div class="inputs five columns omega">
                        <p class="explanation">Enable or disable file uploads through the public contribution form. If set to &#8220;Required,&#8220; users must add a file to their contribution when selecting this item type.</p>
                        <div class="input-block">
                           ' . $this->view->formSelect('file_permissions', $typeRecord->file_permissions, array(), ContributionType::getPossibleFilePermissions()) . '
                        </div>
                    </div>
                 </div>';  
        
        $form .= "</section>";

        $form .= "<section class='three columns omega'>";
        $form .= "<div id='save' class='panel'>";
        
        $form .= '<input type="submit" class="big green button" value="Save" id="submit" name="submit">';
        if($typeRecord->exists()) {
            $form .= link_to($typeRecord, 'delete-confirm', 'Delete', array('class' => 'big red button delete-confirm'));
        }
        $form .= "</div>";
        $form .= "</section>";
      
        return $form;
    }
    
    private function _processForm($record)
    {
        $contributionElTable = $this->_helper->db->getTable('ContributionTypeElement');
        if ($this->getRequest()->isPost()) {
            try {
                $record->setPostData($_POST);
                if ($record->save()) {
                    foreach($_POST['elements-to-add'] as $tempId=>$elementInfo) {
                        debug('saving');
                        debug(print_r($elementInfo, true));
                        $contributionEl = new ContributionTypeElement();
                        $contributionEl->element_id = $elementInfo['id'];
                        $contributionEl->prompt = $elementInfo['prompt'];
                        $contributionEl->order = $elementInfo['order'];
                        $contributionEl->type_id = $record->id;
                        $contributionEl->save();
                    }
                    
                    foreach($_POST['elements'] as $id=>$info) {
                        $contributionEl = $contributionElTable->find($id);
                        $contributionEl->prompt = $info['prompt'];
                        $contributionEl->order = $info['order'];
                        $contributionEl->save();
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
