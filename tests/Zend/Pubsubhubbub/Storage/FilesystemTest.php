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

    public function testCanStoreToken()
    {
        $this->_store->setToken('key1', 'value1');
        $this->assertEquals('value1', $this->_store->getToken('key1'));
    }

    public function testCanCheckExistenceOfToken()
    {
        $this->_store->setToken('key2', 'value2');
        $this->assertTrue($this->_store->hasToken('key2'));
    }

    public function testCanDeleteToken()
    {
        $this->_store->setToken('key3', 'value3');
        $this->_store->removeToken('key3');
        $this->assertFalse($this->_store->hasToken('key3'));
    }

    public function testCanStoreSubscription()
    {
        $this->_store->setSubscription('key1', array(1,2,3));
        $this->assertEquals(array(1,2,3), $this->_store->getSubscription('key1'));
    }

    public function testCanCheckExistenceOfSubscription()
    {
        $this->_store->setSubscription('key2', array(1,2,3));
        $this->assertTrue($this->_store->hasSubscription('key2'));
    }

    public function testCanDeleteSubscription()
    {
        $this->_store->setSubscription('key3', array(1,2,3));
        $this->_store->removeSubscription('key3');
        $this->assertFalse($this->_store->hasSubscription('key3'));
    }

}
