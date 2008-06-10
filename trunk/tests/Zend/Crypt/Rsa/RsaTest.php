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
        $this->_testPemPath128 = realpath('./_file/test128.pem');
    }

    public function testConstructorSetsPemString() 
    {
        $rsa = new Zend_Crypt_Rsa(
            array(
                'pemString'=>$this->_testPemString128
            )
        );
        $this->assertEquals($this->_testPemString128, $rsa->getPemString());
    }

    public function testConstructorSetsPemPath() 
    {
        $rsa = new Zend_Crypt_Rsa(
            array(
                'pemPath'=>$this->_testPemPath128
            )
        );
        $this->assertEquals($this->_testPemPath128, $rsa->getPemString());
    }

    public function testConstructorSetsPemUrl() 
    {
        $rsa = new Zend_Crypt_Rsa(
            array(
                'pemUrl'=>'http://www.example.com/rsa'
            )
        );
        $this->assertEquals('http://www.example.com/rsa', $rsa->getPemUrl());
    }

    public function testConstructorSetsHashOption() 
    {
        $rsa = new Zend_Crypt_Rsa(
            array(
                'hashAlgorithm'=>'sha256'
            )
        );
        $this->assertEquals('sha256', $rsa->getHashAlgorithm());
    }

    public function ttEncryptionOnString() 
    {
        $data = 'I am a plain text string!';
        $rsa = new Zend_Crypt_Rsa(array('pemString'=>$this->_testPemString128));
    }

}