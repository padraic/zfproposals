<?php

require_once 'PHPUnit/Framework/TestCase.php';
require_once 'Zend/Pubsubhubbub/Subscriber/Callback.php';
require_once 'Zend/Pubsubhubbub/StorageInterface.php';

class Zend_Pubsubhubbub_Subscriber_CallbackTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->_callback = new Zend_Pubsubhubbub_Subscriber_Callback;
        $this->_callback->setStorage(new Zend_Pubsubhubbub_Subscriber_CallbackTestStorageHas);
        $this->_get = array( // a full valid array
            'hub.mode' => 'subscribe',
            'hub.topic' => 'http://www.example.com/topic',
            'hub.challenge' => 'abc',
            'hub.verify_token' => 'cba',
            'hub.mode' => 'subscribe',
            'hub.lease_seconds' => '1234567'
        );
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

    public function testValidatesValidHttpGetData()
    {
        $this->assertTrue($this->_callback->isValid($this->_get));
    }

    public function testReturnsFalseIfModeMissingFromHttpGetData()
    {
        unset($this->_get['hub.mode']);
        $this->assertFalse($this->_callback->isValid($this->_get));
    }

    public function testReturnsFalseIfTopicMissingFromHttpGetData()
    {
        unset($this->_get['hub.topic']);
        $this->assertFalse($this->_callback->isValid($this->_get));
    }

    public function testReturnsFalseIfChallengeMissingFromHttpGetData()
    {
        unset($this->_get['hub.challenge']);
        $this->assertFalse($this->_callback->isValid($this->_get));
    }

    public function testReturnsFalseIfVerifyTokenMissingFromHttpGetData()
    {
        unset($this->_get['hub.verify_token']);
        $this->assertFalse($this->_callback->isValid($this->_get));
    }

    public function testReturnsTrueIfModeSetAsUnsubscribeFromHttpGetData()
    {
        $this->_get['hub.mode'] = 'unsubscribe';
        $this->assertTrue($this->_callback->isValid($this->_get));
    }

    public function testReturnsFalseIfModeNotRecognisedFromHttpGetData()
    {
        $this->_get['hub.mode'] = 'abc';
        $this->assertFalse($this->_callback->isValid($this->_get));
    }

    public function testReturnsFalseIfLeaseSecondsMissedWhenModeIsSubscribeFromHttpGetData()
    {
        unset($this->_get['hub.lease_seconds']);
        $this->assertFalse($this->_callback->isValid($this->_get));
    }

    public function testReturnsFalseIfHubTopicInvalidFromHttpGetData()
    {
        $this->_get['hub.topic'] = 'http://';
        $this->assertFalse($this->_callback->isValid($this->_get));
    }

    public function testReturnsFalseIfVerifyTokenRecordDoesNotExistForConfirmRequest()
    {
        $this->_callback->setStorage(new Zend_Pubsubhubbub_Subscriber_CallbackTestStorageHasNot);
        $this->assertFalse($this->_callback->isValid($this->_get));
    }

    public function testReturnsFalseIfVerifyTokenRecordDoesNotAgreeWithConfirmRequest()
    {
        $this->_callback->setStorage(new Zend_Pubsubhubbub_Subscriber_CallbackTestStorageHasButWrong);
        $this->assertFalse($this->_callback->isValid($this->_get));
    }



}


/**
 * Stubs for storage access
 */
class Zend_Pubsubhubbub_Subscriber_CallbackTestStorageHas implements Zend_Pubsubhubbub_StorageInterface
{
    public function store($data, $type, $topicUrl, $hubUrl = null){}
    public function get($type, $topicUrl, $hubUrl = null){return 'cba';}
    public function exists($type, $topicUrl, $hubUrl = null){return true;}
    public function remove($type, $topicUrl, $hubUrl = null){}
    public function cleanup($type){}
}
class Zend_Pubsubhubbub_Subscriber_CallbackTestStorageHasNot implements Zend_Pubsubhubbub_StorageInterface
{
    public function store($data, $type, $topicUrl, $hubUrl = null){}
    public function get($type, $topicUrl, $hubUrl = null){}
    public function exists($type, $topicUrl, $hubUrl = null){return false;}
    public function remove($type, $topicUrl, $hubUrl = null){}
    public function cleanup($type){}
}
class Zend_Pubsubhubbub_Subscriber_CallbackTestStorageHasButWrong implements Zend_Pubsubhubbub_StorageInterface
{
    public function store($data, $type, $topicUrl, $hubUrl = null){}
    public function get($type, $topicUrl, $hubUrl = null){return 'wrong';}
    public function exists($type, $topicUrl, $hubUrl = null){return true;}
    public function remove($type, $topicUrl, $hubUrl = null){}
    public function cleanup($type){}
}
