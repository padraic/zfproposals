<?php

require_once 'PHPUnit/Framework/TestCase.php';
require_once 'Zend/Pubsubhubbub/HubServer/Callback.php';
require_once 'Zend/Pubsubhubbub/StorageInterface.php';
require_once 'Zend/Http/Client/Adapter/Test.php';

class Zend_Pubsubhubbub_HubServer_CallbackTest extends PHPUnit_Framework_TestCase
{

    protected $_adapter = null;
    protected $_originalServer = null;

    protected $_subRequest =
            'hub.callback=http%3A%2F%2Fwww.example.com%2Fcallback%2F5536df06b5dcb966edab3a4c4d56213c16a8184b&hub.lease_seconds=2592000&hub.mode=subscribe&hub.topic=http%3A%2F%2Fwww.example.com%2Ftopic&hub.verify=sync&hub.verify=async&hub.verify_token=abc';

    public function setUp()
    {
        $this->_adapter = new Zend_Http_Client_Adapter_Test;
        $client = new Zend_Http_Client;
        $client->setAdapter($this->_adapter);
        Zend_Pubsubhubbub::setHttpClient($client);
        $this->_callback = new Zend_Pubsubhubbub_HubServer_Callback;
        $this->_callback->setStorage(new Zend_Pubsubhubbub_Storage_Filesystem);
        $this->_originalServer = $_SERVER;
        $_SERVER['REQUEST_METHOD'] = 'POST';
    }

    public function tearDown()
    {
        $_SERVER = $this->_originalServer;
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

    public function testRespondsToSubscriptionRequestMissingCallbackWith404()
    {
        $this->_callback->handle();
        $this->assertEquals(404, $this->_callback->getHttpResponse()->getHttpResponseCode());
    }

    /*public function testRespondsToSubscriptionRequestMissingModeWith404()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $GLOBALS['HTTP_RAW_POST_DATA'] =
            'hub.callback=http%3A%2F%2Fwww.example.com%2Fcallback%2F5536df06b5d'
            .'cb966edab3a4c4d56213c16a8184b&hub.lease_seconds=2592000'
            .'&hub.topic=http%3A%2F%2Fwww.example.com%2Ftopic&hub.veri'
            .'fy=sync&hub.verify=async&hub.verify_token=abc';
    }

    public function testRespondsToSubscriptionRequestMissingTopicWith404()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $GLOBALS['HTTP_RAW_POST_DATA'] =
            'hub.callback=http%3A%2F%2Fwww.example.com%2Fcallback%2F5536df06b5d'
            .'cb966edab3a4c4d56213c16a8184b&hub.lease_seconds=2592000&hub.mode='
            .'subscribe&hub.veri'
            .'fy=sync&hub.verify=async&hub.verify_token=abc';
    }

    public function testRespondsToSubscriptionRequestMissingVerifyWith404()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $GLOBALS['HTTP_RAW_POST_DATA'] =
            'hub.callback=http%3A%2F%2Fwww.example.com%2Fcallback%2F5536df06b5d'
            .'cb966edab3a4c4d56213c16a8184b&hub.lease_seconds=2592000&hub.mode='
            .'subscribe&hub.topic=http%3A%2F%2Fwww.example.com%2Ftopic'
            .'&hub.verify_token=abc';
    }*/

}

/**
 * Stubs for storage access
 */
class Zend_Pubsubhubbub_HubServer_CallbackTestStorageHas implements Zend_Pubsubhubbub_StorageInterface
{
    public function setToken($key, $token){}
    public function getToken($key){
        if ($key == 'verifytokenkey') return hash('sha256', 'cba');
    }
    public function hasToken($key){return true;}
    public function removeToken($key){}
    public function cleanup($type){}
}
class Zend_Pubsubhubbub_HubServer_CallbackTestStorageHasNot implements Zend_Pubsubhubbub_StorageInterface
{
    public function setToken($key, $token){}
    public function getToken($key){}
    public function hasToken($key){return false;}
    public function removeToken($key){}
    public function cleanup($type){}
}
class Zend_Pubsubhubbub_HubServer_CallbackTestStorageHasButWrong implements Zend_Pubsubhubbub_StorageInterface
{
    public function setToken($key, $token){}
    public function getToken($key){return 'wrong';}
    public function hasToken($key){return true;}
    public function removeToken($key){}
    public function cleanup($type){}
}
