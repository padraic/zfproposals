<?php

require_once 'PHPUnit/Framework/TestCase.php';
require_once 'Zend/Pubsubhubbub/HubServer.php';

class Zend_Pubsubhubbub_HubServerTest extends PHPUnit_Framework_TestCase
{

    protected $_hub = null;

    public function setUp()
    {
        $client = new Zend_Http_Client;
        Zend_Pubsubhubbub::setHttpClient($client);
        $this->_hub = new Zend_Pubsubhubbub_HubServer;
    }

    public function testCanSetCallbackUrl()
    {
        $this->_hub->setCallbackUrl('http://www.example.com/callback');
        $this->assertEquals('http://www.example.com/callback', $this->_hub->getCallbackUrl());
    }

    public function testThrowsExceptionOnSettingEmptyCallbackUrl()
    {
        try {
            $this->_hub->setCallbackUrl('');
            $this->fail('Should not fail as an Exception would be raised and caught');
        } catch (Zend_Pubsubhubbub_Exception $e) {}
    }


    public function testThrowsExceptionOnSettingNonStringCallbackUrl()
    {
        try {
            $this->_hub->setCallbackUrl(123);
            $this->fail('Should not fail as an Exception would be raised and caught');
        } catch (Zend_Pubsubhubbub_Exception $e) {}
    }


    public function testThrowsExceptionOnSettingInvalidCallbackUrl()
    {
        try {
            $this->_hub->setCallbackUrl('http://');
            $this->fail('Should not fail as an Exception would be raised and caught');
        } catch (Zend_Pubsubhubbub_Exception $e) {}
    }

    public function testThrowsExceptionOnMissingCallbackUrl()
    {
        try {
            $this->_hub->getCallbackUrl();
            $this->fail('Should not fail as an Exception would be raised and caught');
        } catch (Zend_Pubsubhubbub_Exception $e) {}
    }


}
