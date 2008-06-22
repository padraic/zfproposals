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

    public function testGetHttpClientResetsAuthorizationHeader()
    {
        $client = new Test_Http_Client_19485876();
        $client->setHeaders('Authorization', 'realm="http://www.example.com",oauth_version="1.0"');
        Zend_Oauth::setHttpClient($client);
        $resetClient = Zend_Oauth::getHttpClient();
        $this->assertEquals(null, $resetClient->getHeader('Authorization'));
    }

    // see: http://wiki.oauth.net/TestCases (Parameter Encoding Tests)

    public function testUrlEncodeCorrectlyEncodesAlnum()
    {
        $string = 'abcABC123';
        $this->assertEquals('abcABC123', Zend_Oauth::urlEncode($string));
    }

    public function testUrlEncodeCorrectlyEncodesUnreserved()
    {
        $string = '-._~';
        $this->assertEquals('-._~', Zend_Oauth::urlEncode($string));
    }

    public function testUrlEncodeCorrectlyEncodesPercentSign()
    {
        $string = '%';
        $this->assertEquals('%25', Zend_Oauth::urlEncode($string));
    }

    public function testUrlEncodeCorrectlyEncodesPlusSign()
    {
        $string = '+';
        $this->assertEquals('%2B', Zend_Oauth::urlEncode($string));
    }

    public function testUrlEncodeCorrectlyEncodesAmpEqualsAndAsterix()
    {
        $string = '&=*';
        $this->assertEquals('%26%3D%2A', Zend_Oauth::urlEncode($string));
    }

    public function testUrlEncodeCorrectlyEncodesSpace()
    {
        $string = ' ';
        $this->assertEquals('%20', Zend_Oauth::urlEncode($string));
    }

    public function testUrlEncodeCorrectlyEncodesLineFeed()
    {
        $string = "\n";
        $this->assertEquals('%0A', Zend_Oauth::urlEncode($string));
    }

    public function testUrlEncodeCorrectlyEncodesU007F()
    {
        $string = chr(127);
        $this->assertEquals('%7F', Zend_Oauth::urlEncode($string));
    }

    public function testUrlEncodeCorrectlyEncodesU0080()
    {
        $string = "\xC2\x80";
        $this->assertEquals('%C2%80', Zend_Oauth::urlEncode($string));
    }

    public function testUrlEncodeCorrectlyEncodesU3001()
    {
        $string = '、';
        $this->assertEquals('%E3%80%81', Zend_Oauth::urlEncode($string));
    }

}