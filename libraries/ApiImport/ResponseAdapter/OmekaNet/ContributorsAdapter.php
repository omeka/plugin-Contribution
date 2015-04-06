<?php

class ApiImport_ResponseAdapter_OmekaNet_ContributorsAdapter extends ApiImport_ResponseAdapter_Omeka_GenericAdapter
{
    protected function setFromResponseData()
    {
        parent::setFromResponseData();
        $this->record->setDottedIpAddress($this->responseData['ip_address']);
    }
}