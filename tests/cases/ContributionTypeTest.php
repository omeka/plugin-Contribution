<?php
require_once CONTRIBUTION_PLUGIN_DIR . '/models/ContributionType.php';

class ContributionTypeTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        // Set a fake Omeka_Db on Omeka_Context to allow us to instantiate
        // Omeka_Records.
        $dbAdapter = new Zend_Test_DbAdapter();
        $db = new Omeka_Db($dbAdapter, 'omeka_');
        Omeka_Context::getInstance()->setDb($db);
    }

    public function testIsFileAllowed()
    {
        // When uninitialized, files should not be allowed.
        $type = new ContributionType;
        $this->assertFalse($type->isFileAllowed());

        $type->file_permissions = ContributionType::FILE_PERMISSION_ALLOWED;
        $this->assertTrue($type->isFileAllowed());

        $type->file_permissions = ContributionType::FILE_PERMISSION_REQUIRED;
        $this->assertTrue($type->isFileAllowed());

        $type->file_permissions = ContributionType::FILE_PERMISSION_DISALLOWED;
        $this->assertFalse($type->isFileAllowed());
    }

    public function testIsFileRequired()
    {
        // When uninitialized, files should not be required.
        $type = new ContributionType;
        $this->assertFalse($type->isFileRequired());

        $type->file_permissions = ContributionType::FILE_PERMISSION_ALLOWED;
        $this->assertFalse($type->isFileRequired());

        $type->file_permissions = ContributionType::FILE_PERMISSION_REQUIRED;
        $this->assertTrue($type->isFileRequired());

        $type->file_permissions = ContributionType::FILE_PERMISSION_DISALLOWED;
        $this->assertFalse($type->isFileRequired());
    }
}
