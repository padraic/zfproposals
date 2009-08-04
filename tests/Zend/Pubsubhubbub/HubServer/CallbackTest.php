<?php

require_once 'PHPUnit/Framework/TestCase.php';
require_once 'Zend/Pubsubhubbub/HubServer/Callback.php';
require_once 'Zend/Pubsubhubbub/StorageInterface.php';

class Zend_Pubsubhubbub_HubServer_CallbackTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $client = new Zend_Http_Client;
        Zend_Pubsubhubbub::setHttpClient($client);
        $this->_callback = new Zend_Pubsubhubbub_HubServer_Callback;
    }

    public function testCanSetHttpResponseObject()
    {
        $this->_callback->setHttpResponse(new Zend_Pubsubhubbub_HttpResponse);
        $this->assertTrue($this->_callback->getHttpResponse() instanceof Zend_Pubsubhubbub_HttpResponse);
    }

    public function testCanUsesDefaultHttpResponseObject()
    {
        $this->assertTrue($this->_callback->getHttpResponse() instanceof Zend_Pubsubhubbub_HttpResponse);
    }

    public function testThrowsExceptionOnInvalidHttpResponseObjectSet()
    {
        try {
            $this->_callback->setHttpResponse(new stdClass);
            $this->fail('Should not fail as an Exception would be raised and caught');
        } catch (Zend_Pubsubhubbub_Exception $e) {}
    }

    public function testThrowsExceptionIfNonObjectSetAsHttpResponseObject()
    {
        try {
            $this->_callback->setHttpResponse('');
            $this->fail('Should not fail as an Exception would be raised and caught');
        } catch (Zend_Pubsubhubbub_Exception $e) {}
    }

    public function testCanSetStorageImplementation()
    {
        $this->_callback->setStorage(new Zend_Pubsubhubbub_Storage_Filesystem);
        $this->assertTrue($this->_callback->getStorage() instanceof Zend_Pubsubhubbub_Storage_Filesystem);
    }

    public function testCanSetCallbackUrl()
    {
        $this->_callback->setCallbackUrl('http://www.example.com/callback');
        $this->assertEquals('http://www.example.com/callback', $this->_callback->getCallbackUrl());
    }

    public function testThrowsExceptionOnSettingEmptyCallbackUrl()
    {
        try {
            $this->_callback->setCallbackUrl('');
            $this->fail('Should not fail as an Exception would be raised and caught');
        } catch (Zend_Pubsubhubbub_Exception $e) {}
    }


    public function testThrowsExceptionOnSettingNonStringCallbackUrl()
    {
        try {
            $this->_callback->setCallbackUrl(123);
            $this->fail('Should not fail as an Exception would be raised and caught');
        } catch (Zend_Pubsubhubbub_Exception $e) {}
    }


    public function testThrowsExceptionOnSettingInvalidCallbackUrl()
    {
        try {
            $this->_callback->setCallbackUrl('http://');
            $this->fail('Should not fail as an Exception would be raised and caught');
        } catch (Zend_Pubsubhubbub_Exception $e) {}
    }

    public function testThrowsExceptionOnMissingCallbackUrl()
    {
        try {
            $this->_callback->getCallbackUrl();
            $this->fail('Should not fail as an Exception would be raised and caught');
        } catch (Zend_Pubsubhubbub_Exception $e) {}
    }

    public function testCanSetLeaseSeconds()
    {
        $this->_callback->setLeaseSeconds('10000');
        $this->assertEquals(10000, $this->_callback->getLeaseSeconds());
    }

    public function testThrowsExceptionOnSettingZeroAsLeaseSeconds()
    {
        try {
            $this->_callback->setLeaseSeconds(0);
            $this->fail('Should not fail as an Exception would be raised and caught');
        } catch (Zend_Pubsubhubbub_Exception $e) {}
    }

    public function testThrowsExceptionOnSettingLessThanZeroAsLeaseSeconds()
    {
        try {
            $this->_callback->setLeaseSeconds(-1);
            $this->fail('Should not fail as an Exception would be raised and caught');
        } catch (Zend_Pubsubhubbub_Exception $e) {}
    }

    public function testThrowsExceptionOnSettingAnyScalarTypeCastToAZeroOrLessIntegerAsLeaseSeconds()
    {
        try {
            $this->_callback->setLeaseSeconds('0aa');
            $this->fail('Should not fail as an Exception would be raised and caught');
        } catch (Zend_Pubsubhubbub_Exception $e) {}
    }

    public function testCanSetSubscriberCount()
    {
        $this->_callback->setSubscriberCount('10000');
        $this->assertEquals(10000, $this->_callback->getSubscriberCount());
    }

    public function testDefaultSubscriberCountIsOne()
    {
        $this->assertEquals(1, $this->_callback->getSubscriberCount());
    }

    public function testThrowsExceptionOnSettingZeroAsSubscriberCount()
    {
        try {
            $this->_callback->setSubscriberCount(0);
            $this->fail('Should not fail as an Exception would be raised and caught');
        } catch (Zend_Pubsubhubbub_Exception $e) {}
    }

    public function testThrowsExceptionOnSettingLessThanZeroAsSubscriberCount()
    {
        try {
            $this->_callback->setSubscriberCount(-1);
            $this->fail('Should not fail as an Exception would be raised and caught');
        } catch (Zend_Pubsubhubbub_Exception $e) {}
    }

    public function testThrowsExceptionOnSettingAnyScalarTypeCastToAZeroOrLessIntegerAsSubscriberCount()
    {
        try {
            $this->_callback->setSubscriberCount('0aa');
            $this->fail('Should not fail as an Exception would be raised and caught');
        } catch (Zend_Pubsubhubbub_Exception $e) {}
    }

    public function testCanSetPreferredVerificationMode()
    {
        $this->_callback->setPreferredVerificationMode(Zend_Pubsubhubbub::VERIFICATION_MODE_ASYNC);
        $this->assertEquals(Zend_Pubsubhubbub::VERIFICATION_MODE_ASYNC, $this->_callback->getPreferredVerificationMode());
    }

    public function testSetsPreferredVerificationModeThrowsExceptionOnSettingBadMode()
    {
        try {
            $this->_callback->setPreferredVerificationMode('abc');
            $this->fail('Should not fail as an Exception would be raised and caught');
        } catch (Zend_Pubsubhubbub_Exception $e) {}
    }

    public function testPreferredVerificationModeDefaultsToSync()
    {
        $this->assertEquals(Zend_Pubsubhubbub::VERIFICATION_MODE_SYNC, $this->_callback->getPreferredVerificationMode());
    }

}
