<?php

require_once 'PHPUnit/Framework/TestCase.php';
require_once 'Zend/Pubsubhubbub.php';

class Zend_Pubsubhubbub_PubsubhubbubTest extends PHPUnit_Framework_TestCase
{

    public function teardown()
    {
        Zend_Pubsubhubbub::clearHttpClient();
    }

    public function testCanSetCustomHttpClient()
    {
        Zend_Pubsubhubbub::setHttpClient(new Test_Http_Client_Pubsub());
        $this->assertType('Test_Http_Client_Pubsub', Zend_Pubsubhubbub::getHttpClient());
    }

    public function testCanDetectHubs()
    {
        $feed = Zend_Feed_Reader::importFile(dirname(__FILE__) . '/_files/rss20.xml');
        $this->assertEquals(array(
            'http://www.example.com/hub', 'http://www.example.com/hub2'
        ), Zend_Pubsubhubbub::detectHubs($feed));
    }

}

class Test_Http_Client_Pubsub extends Zend_Http_Client {}
