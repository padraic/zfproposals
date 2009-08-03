<?php

require_once 'PHPUnit/Framework/TestCase.php';
require_once 'Zend/Pubsubhubbub/Storage/Filesystem.php';

class Zend_Pubsubhubbub_Storage_FilesystemTest extends PHPUnit_Framework_TestCase
{
    protected $_store = null;

    public function setUp()
    {
        $this->_store = new Zend_Pubsubhubbub_Storage_Filesystem;
    }

    public function testImplementsStorageInterface()
    {
        $this->assertTrue($this->_store instanceof Zend_Pubsubhubbub_StorageInterface);
    }

    public function testCanStoreVerifyToken()
    {
        $this->_store->setVerifyToken('key1', 'value1');
        $this->assertEquals('value1', $this->_store->getVerifyToken('key1'));
    }

    public function testCanCheckExistenceOfVerifyToken()
    {
        $this->_store->setVerifyToken('key2', 'value2');
        $this->assertTrue($this->_store->hasVerifyToken('key2'));
    }

    public function testCanDeleteVerifyToken()
    {
        $this->_store->setVerifyToken('key3', 'value3');
        $this->_store->removeVerifyToken('key3');
        $this->assertFalse($this->_store->hasVerifyToken('key3'));
    }

}
