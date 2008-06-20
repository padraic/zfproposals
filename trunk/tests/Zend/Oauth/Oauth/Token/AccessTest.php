<?php

require_once 'PHPUnit/Framework/TestCase.php';
require_once 'Zend/Oauth/Token/Access.php';

class Zend_Oauth_Token_AccessTest extends PHPUnit_Framework_TestCase
{

    public function testConstructorSetsInputData()
    {
        $data = array('foo'=>'bar');
        $token = new Zend_Oauth_Token_Access($data);
        $this->assertEquals($data, $token->getData());
    }

    public function testConstructorParsesAccessTokenFromInputData()
    {
        $data = array(
            'oauth_token'=>'jZaee4GF52O3lUb9'
        );
        $token = new Zend_Oauth_Token_Access($data);
        $this->assertEquals('jZaee4GF52O3lUb9', $token->getToken());
    }

    public function testPropertyAccessWorks()
    {
        $data = array(
            'oauth_token'=>'jZaee4GF52O3lUb9'
        );
        $token = new Zend_Oauth_Token_Access($data);
        $this->assertEquals('jZaee4GF52O3lUb9', $token->oauth_token);
    }

    public function testTokenCastsToEncodedQueryString()
    {
        $queryString = 'oauth_token=jZaee4GF52O3lUb9&foo+=bar~';
        $token = new Zend_Oauth_Token_Access();
        $token->setToken('jZaee4GF52O3lUb9');
        $token->setParam('foo ', 'bar~');
        $this->assertEquals($queryString, (string) $token);
    }

    public function testToStringReturnsEncodedQueryString()
    {
        $queryString = 'oauth_token=jZaee4GF52O3lUb9';
        $token = new Zend_Oauth_Token_Access();
        $token->setToken('jZaee4GF52O3lUb9');
        $this->assertEquals($queryString, $token->toString());
    }

    public function testIsValidDetectsBadResponse()
    {
        $data = array(
            'missing_oauth_token'=>'jZaee4GF52O3lUb9'
        );
        $token = new Zend_Oauth_Token_Access($data);
        $this->assertFalse($token->isValid());
    }

    public function testIsValidDetectsGoodResponse()
    {
        $data = array(
            'oauth_token'=>'jZaee4GF52O3lUb9',
            'foo'=>'bar'
        );
        $token = new Zend_Oauth_Token_Access($data);
        $this->assertTrue($token->isValid());
    }

}