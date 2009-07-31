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

}
