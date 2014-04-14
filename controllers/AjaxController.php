<?php
/**
 * @package Contribution
 */

/**
 * Controller for Ajax management.
 */
class Contribution_AjaxController extends Omeka_Controller_AbstractActionController
{
    /**
     * Controller-wide initialization. Sets the underlying model to use.
     */
    public function init()
    {
        // Don't render the view script.
        $this->_helper->viewRenderer->setNoRender(true);

        $this->_helper->db->setDefaultModelName('ContributionContributedItem');
    }

    /**
     * Handle AJAX requests to update a record.
     */
    public function updateAction()
    {
        if (!$this->_checkAjax('update')) {
            return;
        }

        // Handle action.
        try {
            $status = $this->_getParam('status');
            // The administrator can only make public/review a public
            // contributed item: he cannot change the choice of the user here
            // (public/private).
            if (!in_array($status, array('proposed', 'approved', 'rejected'))) {
                $this->getResponse()->setHttpResponseCode(400);
                return;
            }

            $id = (integer) $this->_getParam('id');
            $contributedItem = get_record_by_id('ContributionContributedItem', $id);
            if (!$contributedItem) {
                $this->getResponse()->setHttpResponseCode(400);
                return;
            }

            // Check if the contributor set his item public or private.
            if (!$contributedItem->public) {
                // $this->getResponse()->setHttpResponseCode(400);
                $this->getResponse()->setBody('private');
                return;
            }

            // Update status (set public or to be reviewed).
            // TODO Currently, only "Public" and "Needs review" status are managed.
            $contributedItem->Item->public = ($status === 'approved') ? 1 : 0;
            $contributedItem->Item->save();
        } catch (Exception $e) {
            $this->getResponse()->setHttpResponseCode(500);
        }
    }

    /**
     * Handle AJAX requests to delete a record.
     */
    public function deleteAction()
    {
        if (!$this->_checkAjax('delete')) {
            return;
        }

        // Handle action.
        try {
            $id = (integer) $this->_getParam('id');
            $contributedItem = get_record_by_id('ContributionContributedItem', $id);
            if (!$contributedItem) {
                $this->getResponse()->setHttpResponseCode(400);
                return;
            }

            // The contributed item is automatically deleted when the item is
            // deleted.
            $contributedItem->Item->delete();
        } catch (Exception $e) {
            $this->getResponse()->setHttpResponseCode(500);
        }
    }

    /**
     * Check AJAX requests.
     *
     * 400 Bad Request
     * 403 Forbidden
     * 500 Internal Server Error
     *
     * @param string $action
     */
    protected function _checkAjax($action)
    {
        // Only allow AJAX requests.
        $request = $this->getRequest();
        if (!$request->isXmlHttpRequest()) {
            $this->getResponse()->setHttpResponseCode(403);
            return false;
        }

        // Allow only valid calls.
        if ($request->getControllerName() != 'ajax'
                || $request->getActionName() != $action
            ) {
            $this->getResponse()->setHttpResponseCode(400);
            return false;
        }

        // Allow only allowed users.

        // In fact, Ajax is used to update or delete items and not to manage
        // contributions directly.
        // TODO Use contribution rights?
        if (!in_array($action, array('update', 'delete'))
                || ($action == 'update' && (!is_allowed('Items', 'edit') || !is_allowed('Items', 'makePublic')))
                || ($action == 'delete' && (!is_allowed('Items', 'edit') || !is_allowed('Items', 'delete')))
            ) {
            $this->getResponse()->setHttpResponseCode(403);
            return false;
        }

        return true;
    }
}
