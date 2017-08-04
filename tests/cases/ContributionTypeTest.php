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
        $bootstrap = new Omeka_Test_Bootstrap;
        $bootstrap->getContainer()->db = $db;
        Zend_Registry::set('bootstrap', $bootstrap);
    }

    public function tearDown()
    {
        Zend_Registry::_unsetInstance();
    }

    public function testIsFileAllowed()
    {
        // When uninitialized, files should not be allowed.
        $type = new ContributionType;
        $this->assertFalse($type->isFileAllowed());

        $type->file_permissions = 'Allowed';
        $this->assertTrue($type->isFileAllowed());

        $type->file_permissions = 'Required';
        $this->assertTrue($type->isFileAllowed());

        $type->file_permissions = 'Disallowed';
        $this->assertFalse($type->isFileAllowed());
    }

    public function testIsFileRequired()
    {
        // When uninitialized, files should not be required.
        $type = new ContributionType;
        $this->assertFalse($type->isFileRequired());

        $type->file_permissions = 'Allowed';
        $this->assertFalse($type->isFileRequired());

        $type->file_permissions = 'Required';
        $this->assertTrue($type->isFileRequired());

        $type->file_permissions = 'Disallowed';
        $this->assertFalse($type->isFileRequired());
    }

    public function testFilePermissionsCoverage()
    {
        $permissions = ContributionType::getPossibleFilePermissions();
        $this->assertAndRemoveArrayKey('Allowed', $permissions);
        $this->assertAndRemoveArrayKey('Required', $permissions);
        $this->assertAndRemoveArrayKey('Disallowed', $permissions);
        $this->assertEquals(0, count($permissions), 'Not all file permission levels are covered by testing.');
    }

    private function assertAndRemoveArrayKey($key, &$array)
    {
        $this->assertArrayHasKey($key, $array);
        unset($array[$key]);
    }
}