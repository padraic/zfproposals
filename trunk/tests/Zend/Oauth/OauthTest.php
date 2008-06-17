<?php

require_once 'PHPUnit/Framework/TestCase.php';
require_once 'Zend/Oauth.php';

class Test_Http_Client_19485876 extends Zend_Http_Client {}

class Zend_OauthTest extends PHPUnit_Framework_TestCase
{

    public function teardown()
    {
        Zend_Oauth::clearHttpClient();
    }

    public function testCanSetCustomHttpClient()
    {
        Zend_Oauth::setHttpClient(new Test_Http_Client_19485876());
        $this->assertType('Test_Http_Client_19485876', Zend_Oauth::getHttpClient());
    }

    public function testGetHttpClientResetsParameters()
    {
        $client = new Test_Http_Client_19485876();
        $client->setParameterGet(array('key'=>'value'));
        Zend_Oauth::setHttpClient($client);
        $resetClient = Zend_Oauth::getHttpClient();
        $resetClient->setUri('http://www.example.com');
        $this->assertEquals('http://www.example.com:80', $resetClient->getUri(true));
    }

}