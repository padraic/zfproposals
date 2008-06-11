<?php

require_once 'Zend/Crypt/Rsa.php';
require_once 'PHPUnit/Framework/TestCase.php';

class Zend_Crypt_RsaTest extends PHPUnit_Framework_TestCase
{

    protected $_testPemString128 = null;

    protected $_testPemPath128 = null;

    public function setup()
    {
        $this->_testPemString128 = <<<RSAKEY
-----BEGIN RSA PRIVATE KEY-----
MF4CAQACEJN4kpcB5WQsKOZNo7dBKh8CAwEAAQIQEIljvsU6qOnRiqXlUDiRmQII
/+7ezbyrmdsCCJOCcWFQPy4NAgiIPFlx3wrhnQIITO8avVuCKD0CCLlTbhq4BGNH
-----END RSA PRIVATE KEY-----
RSAKEY;

        $this->_testPemString128Public = <<<RSAKEY
-----BEGIN PUBLIC KEY-----
MCwwDQYJKoZIhvcNAQEBBQADGwAwGAIRAJN4kpcB5WQsKOZNo7dBKh8CAwEAAQ==
-----END PUBLIC KEY-----

RSAKEY;

        $this->_testPemPath128 = dirname(__FILE__) . '/_files/test128.pem';
    }

    public function testConstructorSetsPemString()
    {
        $rsa = new Zend_Crypt_Rsa(array('pemString'=>$this->_testPemString128));
        $this->assertEquals($this->_testPemString128, $rsa->getPemString());
    }

    public function testConstructorSetsPemPath()
    {
        $rsa = new Zend_Crypt_Rsa(array('pemPath'=>$this->_testPemPath128));
        $this->assertEquals($this->_testPemPath128, $rsa->getPemPath());
    }

    public function testSetPemPathLoadsPemString()
    {
        $rsa = new Zend_Crypt_Rsa(array('pemPath'=>$this->_testPemPath128));
        $this->assertEquals($this->_testPemString128, $rsa->getPemString());
    }

    public function testConstructorSetsPemUrl()
    {
        $rsa = new Zend_Crypt_Rsa(array('pemUrl'=>'http://www.example.com/rsa'));
        $this->assertEquals('http://www.example.com/rsa', $rsa->getPemUrl());
    }

    public function testConstructorSetsHashOption()
    {
        $rsa = new Zend_Crypt_Rsa(array('hashAlgorithm'=>'sha256'));
        $this->assertEquals('sha256', $rsa->getHashAlgorithm());
    }

    public function testSetPemStringParsesPemForPrivateKey()
    {
        $rsa = new Zend_Crypt_Rsa(array('pemString'=>$this->_testPemString128));
        $this->assertEquals('OpenSSL key', get_resource_type($rsa->getPrivateKey()));
    }

    public function testSetPemStringParsesPemForPublicKeyAsPemString()
    {
        $rsa = new Zend_Crypt_Rsa(array('pemString'=>$this->_testPemString128));
        $this->assertEquals($this->_testPemString128Public, $rsa->getPublicKey());
    }

    public function test()
    {
    }

}