<?php

require_once 'PHPUnit/Framework/TestCase.php';
require_once 'Zend/Pubsubhubbub/Subscriber.php';

class Zend_Pubsubhubbub_SubscriberTest extends PHPUnit_Framework_TestCase
{

    protected $_subscriber = null;

    public function setUp()
    {
        $client = new Zend_Http_Client;
        Zend_Pubsubhubbub::setHttpClient($client);
        $this->_subscriber = new Zend_Pubsubhubbub_Subscriber;
    }


    public function testAddsHubServerUrl()
    {
        $this->_subscriber->addHubUrl('http://www.example.com/hub');
        $this->assertEquals(array('http://www.example.com/hub'), $this->_subscriber->getHubUrls());
    }

    public function testAddsHubServerUrlsFromArray()
    {
        $this->_subscriber->addHubUrls(array(
            'http://www.example.com/hub', 'http://www.example.com/hub2'
        ));
        $this->assertEquals(array(
            'http://www.example.com/hub', 'http://www.example.com/hub2'
        ), $this->_subscriber->getHubUrls());
    }

    public function testAddsHubServerUrlsFromArrayUsingSetConfig()
    {
        $this->_subscriber->setConfig(array('hubUrls' => array(
            'http://www.example.com/hub', 'http://www.example.com/hub2'
        )));
        $this->assertEquals(array(
            'http://www.example.com/hub', 'http://www.example.com/hub2'
        ), $this->_subscriber->getHubUrls());
    }

    public function testRemovesHubServerUrl()
    {
        $this->_subscriber->addHubUrls(array(
            'http://www.example.com/hub', 'http://www.example.com/hub2'
        ));
        $this->_subscriber->removeHubUrl('http://www.example.com/hub');
        $this->assertEquals(array(
            1 => 'http://www.example.com/hub2'
        ), $this->_subscriber->getHubUrls());
    }

    public function testRetrievesUniqueHubServerUrlsOnly()
    {
        $this->_subscriber->addHubUrls(array(
            'http://www.example.com/hub', 'http://www.example.com/hub2',
            'http://www.example.com/hub'
        ));
        $this->assertEquals(array(
            'http://www.example.com/hub', 'http://www.example.com/hub2'
        ), $this->_subscriber->getHubUrls());
    }

    public function testThrowsExceptionOnSettingEmptyHubServerUrl()
    {
        try {
            $this->_subscriber->addHubUrl('');
            $this->fail('Should not fail as an Exception would be raised and caught');
        } catch (Zend_Pubsubhubbub_Exception $e) {}
    }

    public function testThrowsExceptionOnSettingNonStringHubServerUrl()
    {
        try {
            $this->_subscriber->addHubUrl(123);
            $this->fail('Should not fail as an Exception would be raised and caught');
        } catch (Zend_Pubsubhubbub_Exception $e) {}
    }

    public function testThrowsExceptionOnSettingInvalidHubServerUrl()
    {
        try {
            $this->_subscriber->addHubUrl('http://');
            $this->fail('Should not fail as an Exception would be raised and caught');
        } catch (Zend_Pubsubhubbub_Exception $e) {}
    }

    public function testAddsParameter()
    {
        $this->_subscriber->setParameter('foo', 'bar');
        $this->assertEquals(array('foo'=>'bar'), $this->_subscriber->getParameters());
    }

    public function testAddsParametersFromArray()
    {
        $this->_subscriber->setParameters(array(
            'foo' => 'bar', 'boo' => 'baz'
        ));
        $this->assertEquals(array(
            'foo' => 'bar', 'boo' => 'baz'
        ), $this->_subscriber->getParameters());
    }

    public function testAddsParametersFromArrayInSingleMethod()
    {
        $this->_subscriber->setParameter(array(
            'foo' => 'bar', 'boo' => 'baz'
        ));
        $this->assertEquals(array(
            'foo' => 'bar', 'boo' => 'baz'
        ), $this->_subscriber->getParameters());
    }

    public function testAddsParametersFromArrayUsingSetConfig()
    {
        $this->_subscriber->setConfig(array('parameters' => array(
            'foo' => 'bar', 'boo' => 'baz'
        )));
        $this->assertEquals(array(
            'foo' => 'bar', 'boo' => 'baz'
        ), $this->_subscriber->getParameters());
    }

    public function testRemovesParameter()
    {
        $this->_subscriber->setParameters(array(
            'foo' => 'bar', 'boo' => 'baz'
        ));
        $this->_subscriber->removeParameter('boo');
        $this->assertEquals(array(
            'foo' => 'bar'
        ), $this->_subscriber->getParameters());
    }

    public function testRemovesParameterIfSetToNull()
    {
        $this->_subscriber->setParameters(array(
            'foo' => 'bar', 'boo' => 'baz'
        ));
        $this->_subscriber->setParameter('boo', null);
        $this->assertEquals(array(
            'foo' => 'bar'
        ), $this->_subscriber->getParameters());
    }

    public function testCanSetTopicUrl()
    {
        $this->_subscriber->setTopicUrl('http://www.example.com/topic');
        $this->assertEquals('http://www.example.com/topic', $this->_subscriber->getTopicUrl());
    }

    public function testThrowsExceptionOnSettingEmptyTopicUrl()
    {
        try {
            $this->_subscriber->setTopicUrl('');
            $this->fail('Should not fail as an Exception would be raised and caught');
        } catch (Zend_Pubsubhubbub_Exception $e) {}
    }


    public function testThrowsExceptionOnSettingNonStringTopicUrl()
    {
        try {
            $this->_subscriber->setTopicUrl(123);
            $this->fail('Should not fail as an Exception would be raised and caught');
        } catch (Zend_Pubsubhubbub_Exception $e) {}
    }


    public function testThrowsExceptionOnSettingInvalidTopicUrl()
    {
        try {
            $this->_subscriber->setTopicUrl('http://');
            $this->fail('Should not fail as an Exception would be raised and caught');
        } catch (Zend_Pubsubhubbub_Exception $e) {}
    }

    public function testCanSetCallbackUrl()
    {
        $this->_subscriber->setCallbackUrl('http://www.example.com/callback');
        $this->assertEquals('http://www.example.com/callback', $this->_subscriber->getCallbackUrl());
    }

    public function testThrowsExceptionOnSettingEmptyCallbackUrl()
    {
        try {
            $this->_subscriber->setCallbackUrl('');
            $this->fail('Should not fail as an Exception would be raised and caught');
        } catch (Zend_Pubsubhubbub_Exception $e) {}
    }


    public function testThrowsExceptionOnSettingNonStringCallbackUrl()
    {
        try {
            $this->_subscriber->setCallbackUrl(123);
            $this->fail('Should not fail as an Exception would be raised and caught');
        } catch (Zend_Pubsubhubbub_Exception $e) {}
    }


    public function testThrowsExceptionOnSettingInvalidCallbackUrl()
    {
        try {
            $this->_subscriber->setCallbackUrl('http://');
            $this->fail('Should not fail as an Exception would be raised and caught');
        } catch (Zend_Pubsubhubbub_Exception $e) {}
    }

    public function testCanSetLeaseSeconds()
    {
        $this->_subscriber->setLeaseSeconds('10000');
        $this->assertEquals(10000, $this->_subscriber->getLeaseSeconds());
    }

    public function testThrowsExceptionOnSettingZeroAsLeaseSeconds()
    {
        try {
            $this->_subscriber->setLeaseSeconds(0);
            $this->fail('Should not fail as an Exception would be raised and caught');
        } catch (Zend_Pubsubhubbub_Exception $e) {}
    }

    public function testThrowsExceptionOnSettingLessThanZeroAsLeaseSeconds()
    {
        try {
            $this->_subscriber->setLeaseSeconds(-1);
            $this->fail('Should not fail as an Exception would be raised and caught');
        } catch (Zend_Pubsubhubbub_Exception $e) {}
    }

    public function testThrowsExceptionOnSettingAnyScalarTypeCastToAZeroOrLessIntegerAsLeaseSeconds()
    {
        try {
            $this->_subscriber->setLeaseSeconds('0aa');
            $this->fail('Should not fail as an Exception would be raised and caught');
        } catch (Zend_Pubsubhubbub_Exception $e) {}
    }

    public function testCanSetPreferredVerificationMode()
    {
        $this->_subscriber->setPreferredVerificationMode(Zend_Pubsubhubbub::VERIFICATION_MODE_ASYNC);
        $this->assertEquals(Zend_Pubsubhubbub::VERIFICATION_MODE_ASYNC, $this->_subscriber->getPreferredVerificationMode());
    }

    public function testSetsPreferredVerificationModeThrowsExceptionOnSettingBadMode()
    {
        try {
            $this->_subscriber->setPreferredVerificationMode('abc');
            $this->fail('Should not fail as an Exception would be raised and caught');
        } catch (Zend_Pubsubhubbub_Exception $e) {}
    }

    public function testPreferredVerificationModeDefaultsToSync()
    {
        $this->assertEquals(Zend_Pubsubhubbub::VERIFICATION_MODE_SYNC, $this->_subscriber->getPreferredVerificationMode());
    }

    public function testCanSetStorageImplementation()
    {
        $this->_subscriber->setStorage(new Zend_Pubsubhubbub_Storage_Filesystem);
        $this->assertTrue($this->_subscriber->getStorage() instanceof Zend_Pubsubhubbub_Storage_Filesystem);
    }

    public function testGetStorageThrowsExceptionIfNoneSet()
    {
        try {
            $this->_subscriber->getStorage();
            $this->fail('Should not fail as an Exception would be raised and caught');
        } catch (Zend_Pubsubhubbub_Exception $e) {}
    }

}
