<?php

require_once 'PHPUnit/Framework/TestCase.php';
require_once 'Zend/Oauth/Token/Access.php';
require_once 'Zend/Oauth/Config/Interface.php';

class Zend_Oauth_Token_AccessTest extends PHPUnit_Framework_TestCase
{

    public function testConstructorSetsResponseObject()
    {
        $response = new Zend_Http_Response(200, array());
        $token = new Zend_Oauth_Token_Access($response);
        $this->assertType('Zend_Http_Response', $token->getResponse());
    }

    public function testConstructorParsesRequestTokenFromResponseBody()
    {
        $body = 'oauth_token=jZaee4GF52O3lUb9&oauth_token_secret=J4Ms4n8sxjYc0A8K0KOQFCTL0EwUQTri';
        $response = new Zend_Http_Response(200, array(), $body);
        $token = new Zend_Oauth_Token_Access($response);
        $this->assertEquals('jZaee4GF52O3lUb9', $token->getToken());
    }

    public function testConstructorParsesRequestTokenSecretFromResponseBody()
    {
        $body = 'oauth_token=jZaee4GF52O3lUb9&oauth_token_secret=J4Ms4n8sxjYc0A8K0KOQFCTL0EwUQTri';
        $response = new Zend_Http_Response(200, array(), $body);
        $token = new Zend_Oauth_Token_Access($response);
        $this->assertEquals('J4Ms4n8sxjYc0A8K0KOQFCTL0EwUQTri', $token->getTokenSecret());
    }

    public function testPropertyAccessWorks()
    {
        $body = 'oauth_token=jZaee4GF52O3lUb9&oauth_token_secret=J4Ms4n8sxjYc0A8K0KOQFCTL0EwUQTri&foo=bar';
        $response = new Zend_Http_Response(200, array(), $body);
        $token = new Zend_Oauth_Token_Access($response);
        $this->assertEquals('J4Ms4n8sxjYc0A8K0KOQFCTL0EwUQTri', $token->oauth_token_secret);
    }

    public function testTokenCastsToEncodedResponseBody()
    {
        $body = 'oauth_token=jZaee4GF52O3lUb9&oauth_token_secret=J4Ms4n8sxjYc0A8K0KOQFCTL0EwUQTri';
        $token = new Zend_Oauth_Token_Access();
        $token->setToken('jZaee4GF52O3lUb9');
        $token->setTokenSecret('J4Ms4n8sxjYc0A8K0KOQFCTL0EwUQTri');
        $this->assertEquals($body, (string) $token);
    }

    public function testToStringReturnsEncodedResponseBody()
    {
        $body = 'oauth_token=jZaee4GF52O3lUb9&oauth_token_secret=J4Ms4n8sxjYc0A8K0KOQFCTL0EwUQTri';
        $token = new Zend_Oauth_Token_Access();
        $token->setToken('jZaee4GF52O3lUb9');
        $token->setTokenSecret('J4Ms4n8sxjYc0A8K0KOQFCTL0EwUQTri');
        $this->assertEquals($body, $token->toString());
    }

    public function testIsValidDetectsBadResponse()
    {
        $body = 'oauthtoken=jZaee4GF52O3lUb9&oauthtokensecret=J4Ms4n8sxjYc0A8K0KOQFCTL0EwUQTri';
        $response = new Zend_Http_Response(200, array(), $body);
        $token = new Zend_Oauth_Token_Access($response);
        $this->assertFalse($token->isValid());
    }

    public function testIsValidDetectsGoodResponse()
    {
        $body = 'oauth_token=jZaee4GF52O3lUb9&oauth_token_secret=J4Ms4n8sxjYc0A8K0KOQFCTL0EwUQTri';
        $response = new Zend_Http_Response(200, array(), $body);
        $token = new Zend_Oauth_Token_Access($response);
        $this->assertTrue($token->isValid());
    }

    public function testToHeaderReturnsValidHeaderString() 
    {
        $token = new Zend_Oauth_Token_Access(null, new Test_Http_Utility_90244);
        $value = $token->toHeader(
            'http://www.example.com',
            new Test_Config_90244
        );
        $this->assertEquals('OAuth realm="",oauth_consumer_key="1234567890",oauth_nonce="e807f1fcf82d132f9bb018ca6738a19f",oauth_signature_method="HMAC-SHA1",oauth_timestamp="12345678901",oauth_token="abcde",oauth_version="1.0",oauth_signature="6fb42da0e32e07b61c9f0251fe627a9c"', $value);
    }

}

class Test_Http_Utility_90244 extends Zend_Oauth_Http_Utility
{
    public function __construct(){}
    public function generateNonce(){return md5('1234567890');}
    public function generateTimestamp(){return '12345678901';}
    public function sign(array $params, $signatureMethod, $consumerSecret,
        $accessTokenSecret = null, $method = null, $url = null)
    {
        return md5('0987654321');
    }
}

class Test_Config_90244 implements Zend_Oauth_Config_Interface
{
    public function getConsumerKey(){return '1234567890';}
    public function getSignatureMethod(){return 'HMAC-SHA1';}
    public function getVersion(){return '1.0';}
    public function getRequestTokenUrl(){return 'http://www.example.com/request';}
    public function getToken(){
        $token = new Zend_Oauth_Token_Access;
        $token->setToken('abcde');
        return $token;
    }
    public function getRequestMethod() 
    {
        return 'POST';
    }
    public function setOptions(array $options){}
    public function setConsumerKey($key){}
    public function setConsumerSecret($secret){}
    public function getConsumerSecret(){}
    public function setSignatureMethod($method){}
    public function setRequestScheme($scheme){}
    public function getRequestScheme(){}
    public function setVersion($version){}
    public function setLocalUrl($url){}
    public function getLocalUrl(){}
    public function setRequestTokenUrl($url){}
    public function setAccessTokenUrl($url){}
    public function getAccessTokenUrl(){}
    public function setUserAuthorisationUrl($url){}
    public function getUserAuthorisationUrl(){}
}