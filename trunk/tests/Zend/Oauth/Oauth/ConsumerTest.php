<?php

require_once 'PHPUnit/Framework/TestCase.php';
require_once 'Zend/Oauth/Consumer.php';

class Zend_Oauth_ConsumerTest extends PHPUnit_Framework_TestCase
{

    public function teardown()
    {
        Zend_Oauth::clearHttpClient();
    }

    public function testConstructorSetsConsumerKey()
    {
        $consumer = new Zend_Oauth_Consumer('1234567890', '0987654321');
        $this->assertEquals('1234567890', $consumer->getConsumerKey());
    }

    public function testConstructorSetsConsumerSecret()
    {
        $consumer = new Zend_Oauth_Consumer('1234567890', '0987654321');
        $this->assertEquals('0987654321', $consumer->getConsumerSecret());
    }

    public function testSetsSignatureMethodFromOptionsArray()
    {
        $options = array(
            'signatureMethod' => 'rsa-sha1'
        );
        $consumer = new Zend_Oauth_Consumer('bleh', 'bleh', $options);
        $this->assertEquals('RSA-SHA1', $consumer->getSignatureMethod());
    }

    /*public function testSetsRequestMethodFromOptionsArray()
    {
        $options = array(
            'requestMethod' => 'get'
        );
        $consumer = new Zend_Oauth_Consumer('bleh', 'bleh', $options);
        $this->assertEquals('GET', $consumer->getRequestMethod());
    }*/

    public function testSetsRequestSchemeFromOptionsArray()
    {
        $options = array(
            'requestScheme' => Zend_Oauth::REQUEST_SCHEME_POSTBODY
        );
        $consumer = new Zend_Oauth_Consumer('bleh', 'bleh', $options);
        $this->assertEquals(Zend_Oauth::REQUEST_SCHEME_POSTBODY, $consumer->getRequestScheme());
    }

    public function testSetsVersionFromOptionsArray()
    {
        $options = array(
            'version' => '1.1'
        );
        $consumer = new Zend_Oauth_Consumer('bleh', 'bleh', $options);
        $this->assertEquals('1.1', $consumer->getVersion());
    }

    public function testSetsLocalUrlFromOptionsArray()
    {
        $options = array(
            'localUrl' => 'http://www.example.com/local'
        );
        $consumer = new Zend_Oauth_Consumer('bleh', 'bleh', $options);
        $this->assertEquals('http://www.example.com/local', $consumer->getLocalUrl());
    }

    public function testSetsRequestTokenUrlFromOptionsArray()
    {
        $options = array(
            'requestTokenUrl' => 'http://www.example.com/request'
        );
        $consumer = new Zend_Oauth_Consumer('bleh', 'bleh', $options);
        $this->assertEquals('http://www.example.com/request', $consumer->getRequestTokenUrl());
    }

    public function testSetsUserAuthorisationUrlFromOptionsArray()
    {
        $options = array(
            'userAuthorisationUrl' => 'http://www.example.com/authorise'
        );
        $consumer = new Zend_Oauth_Consumer('bleh', 'bleh', $options);
        $this->assertEquals('http://www.example.com/authorise', $consumer->getUserAuthorisationUrl());
    }

    public function testSetsAccessTokenUrlFromOptionsArray()
    {
        $options = array(
            'accessTokenUrl' => 'http://www.example.com/access'
        );
        $consumer = new Zend_Oauth_Consumer('bleh', 'bleh', $options);
        $this->assertEquals('http://www.example.com/access', $consumer->getAccessTokenUrl());
    }

    public function testSetSignatureMethodThrowsExceptionForInvalidMethod()
    {
        $consumer = new Zend_Oauth_Consumer('12345', '54321');
        try {
            $consumer->setSignatureMethod('buckyball');
            $this->fail('Invalid signature method accepted by setSignatureMethod');
        } catch (Zend_Oauth_Exception $e) {
        }
    }

    /*public function testSetRequestMethodThrowsExceptionForInvalidMethod()
    {
        $consumer = new Zend_Oauth_Consumer('12345', '54321');
        try {
            $consumer->setRequestMethod('buckyball');
            $this->fail('Invalid request method accepted by setRequestMethod');
        } catch (Zend_Oauth_Exception $e) {
        }
    }*/

    public function testSetRequestSchemeThrowsExceptionForInvalidMethod()
    {
        $consumer = new Zend_Oauth_Consumer('12345', '54321');
        try {
            $consumer->setRequestScheme('buckyball');
            $this->fail('Invalid request scheme accepted by setRequestScheme');
        } catch (Zend_Oauth_Exception $e) {
        }
    }

    public function testSetLocalUrlThrowsExceptionForInvalidUrl()
    {
        $consumer = new Zend_Oauth_Consumer('12345', '54321');
        try {
            $consumer->setLocalUrl('buckyball');
            $this->fail('Invalid url accepted by setLocalUrl');
        } catch (Zend_Oauth_Exception $e) {
        }
    }

    public function testSetRequestTokenUrlThrowsExceptionForInvalidUrl()
    {
        $consumer = new Zend_Oauth_Consumer('12345', '54321');
        try {
            $consumer->setRequestTokenUrl('buckyball');
            $this->fail('Invalid url accepted by setRequestUrl');
        } catch (Zend_Oauth_Exception $e) {
        }
    }

    public function testSetUserAuthorisationUrlThrowsExceptionForInvalidUrl()
    {
        $consumer = new Zend_Oauth_Consumer('12345', '54321');
        try {
            $consumer->setUserAuthorisationUrl('buckyball');
            $this->fail('Invalid url accepted by setUserAuthorisationUrl');
        } catch (Zend_Oauth_Exception $e) {
        }
    }

    public function testSetAccessTokenUrlThrowsExceptionForInvalidUrl()
    {
        $consumer = new Zend_Oauth_Consumer('12345', '54321');
        try {
            $consumer->setAccessTokenUrl('buckyball');
            $this->fail('Invalid url accepted by setAccessTokenUrl');
        } catch (Zend_Oauth_Exception $e) {
        }
    }

    public function testGetRequestTokenReturnsInstanceOfOauthTokenRequest()
    {
        $consumer = new Zend_Oauth_Consumer('12345', '54321');
        $token = $consumer->getRequestToken(null, new Test_Http_RequestToken_48231);
        $this->assertType('Zend_Oauth_Token_Request', $token);
    }

    public function testGetRedirectUrlReturnsUserAuthorisationUrlWithParameters()
    {
        $consumer = new Test_Consumer_48231('12345', '54321', array(
            'userAuthorisationUrl'=>'http://www.example.com/authorise'
        ));
        $params = array('foo'=>'bar');
        $uauth = new Zend_Oauth_Http_UserAuthorisation($consumer, $params);
        $token = new Zend_Oauth_Token_Request;
        $token->setParams(array('oauth_token'=>'123456', 'oauth_token_secret'=>'654321'));
        $redirectUrl = $consumer->getRedirectUrl($params, $token, $uauth);
        $this->assertEquals(
            'http://www.example.com/authorise?oauth_token=123456&oauth_callback=http%3A%2F%2Fwww.example.com%2Flocal&foo=bar',
            $redirectUrl
        );
    }

    public function testGetAccessTokenReturnsInstanceOfOauthTokenAccess()
    {
        $consumer = new Zend_Oauth_Consumer('12345', '54321');
        $rtoken = new Zend_Oauth_Token_Request;
        $rtoken->setToken('token');
        $token = $consumer->getAccessToken(array('oauth_token'=>'token'), $rtoken, new Test_Http_AccessToken_48231);
        $this->assertType('Zend_Oauth_Token_Access', $token);
    }

    public function testGetLastRequestTokenReturnsInstanceWhenExists()
    {
        $consumer = new Test_Consumer_48231('12345', '54321');
        $this->assertType('Zend_Oauth_Token_Request', $consumer->getLastRequestToken());
    }

    public function testGetLastAccessTokenReturnsInstanceWhenExists()
    {
        $consumer = new Test_Consumer_48231('12345', '54321');
        $this->assertType('Zend_Oauth_Token_Access', $consumer->getLastAccessToken());
    }

}

class Test_Http_RequestToken_48231 extends Zend_Oauth_Http_RequestToken
{
    public function __construct(){}
    public function execute(array $params = null){
        $return = new Zend_Oauth_Token_Request;
        return $return;}
    public function setParams(array $customServiceParameters){}
}

class Test_Http_AccessToken_48231 extends Zend_Oauth_Http_AccessToken
{
    public function __construct(){}
    public function execute(array $params = null){
        $return = new Zend_Oauth_Token_Access;
        return $return;}
    public function setParams(array $customServiceParameters){}
}

class Test_Consumer_48231 extends Zend_Oauth_Consumer
{
    public function __construct($consumerKey, $consumerSecret, array $options = array()){
        $this->_requestToken = new Zend_Oauth_Token_Request;
        $this->_accessToken = new Zend_Oauth_Token_Access;
        parent::__construct($consumerKey, $consumerSecret, $options);}
    public function getLocalUrl(){
        return 'http://www.example.com/local';}
}