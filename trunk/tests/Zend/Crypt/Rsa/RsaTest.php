<?php

require_once 'Zend/Crypt/Rsa.php';
require_once 'PHPUnit/Framework/TestCase.php';

class Zend_Crypt_RsaTest extends PHPUnit_Framework_TestCase
{

    protected $_testPemString = null;

    protected $_testPemPath = null;

    public function setup()
    {
        $this->_testPemString = <<<RSAKEY
-----BEGIN RSA PRIVATE KEY-----
MIIBOgIBAAJBANDiE2+Xi/WnO+s120NiiJhNyIButVu6zxqlVzz0wy2j4kQVUC4Z
RZD80IY+4wIiX2YxKBZKGnd2TtPkcJ/ljkUCAwEAAQJAL151ZeMKHEU2c1qdRKS9
sTxCcc2pVwoAGVzRccNX16tfmCf8FjxuM3WmLdsPxYoHrwb1LFNxiNk1MXrxjH3R
6QIhAPB7edmcjH4bhMaJBztcbNE1VRCEi/bisAwiPPMq9/2nAiEA3lyc5+f6DEIJ
h1y6BWkdVULDSM+jpi1XiV/DevxuijMCIQCAEPGqHsF+4v7Jj+3HAgh9PU6otj2n
Y79nJtCYmvhoHwIgNDePaS4inApN7omp7WdXyhPZhBmulnGDYvEoGJN66d0CIHra
I2SvDkQ5CmrzkW5qPaE2oO7BSqAhRZxiYpZFb5CI
-----END RSA PRIVATE KEY-----

RSAKEY;

        $this->_testPemStringPublic = <<<RSAKEY
-----BEGIN PUBLIC KEY-----
MFwwDQYJKoZIhvcNAQEBBQADSwAwSAJBANDiE2+Xi/WnO+s120NiiJhNyIButVu6
zxqlVzz0wy2j4kQVUC4ZRZD80IY+4wIiX2YxKBZKGnd2TtPkcJ/ljkUCAwEAAQ==
-----END PUBLIC KEY-----

RSAKEY;

        $this->_testPemPath = dirname(__FILE__) . '/_files/test.pem';
    }

    public function testConstructorSetsPemString()
    {
        $rsa = new Zend_Crypt_Rsa(array('pemString'=>$this->_testPemString));
        $this->assertEquals($this->_testPemString, $rsa->getPemString());
    }

    public function testConstructorSetsPemPath()
    {
        $rsa = new Zend_Crypt_Rsa(array('pemPath'=>$this->_testPemPath));
        $this->assertEquals($this->_testPemPath, $rsa->getPemPath());
    }

    public function testSetPemPathLoadsPemString()
    {
        $rsa = new Zend_Crypt_Rsa(array('pemPath'=>$this->_testPemPath));
        $this->assertEquals($this->_testPemString, $rsa->getPemString());
    }

    public function testConstructorSetsHashOption()
    {
        $rsa = new Zend_Crypt_Rsa(array('hashAlgorithm'=>'md2'));
        $this->assertEquals(OPENSSL_ALGO_MD2, $rsa->getHashAlgorithm());
    }

    public function testSetPemStringParsesPemForPrivateKey()
    {
        $rsa = new Zend_Crypt_Rsa(array('pemString'=>$this->_testPemString));
        $this->assertType('Zend_Crypt_Rsa_Key_Private', $rsa->getPrivateKey());
    }

    public function testSetPemStringParsesPemForPublicKey()
    {
        $rsa = new Zend_Crypt_Rsa(array('pemString'=>$this->_testPemString));
        $this->assertType('Zend_Crypt_Rsa_Key_Public', $rsa->getPublicKey());
    }

    public function testSignGeneratesExpectedBinarySignature()
    {
        $rsa = new Zend_Crypt_Rsa(array('pemString'=>$this->_testPemString));
        $signature = $rsa->sign('1234567890');
        $this->assertEquals(
        'sMHpp3u6DNecIm5RIkDD3xyKaH6qqP8roUWDs215iOGHehfK1ypqwoETKNP7NaksGS2C1Up813ixlGXkipPVbQ==',
        base64_encode($signature));
    }

    public function testSignGeneratesExpectedBase64Signature()
    {
        $rsa = new Zend_Crypt_Rsa(array('pemString'=>$this->_testPemString));
        $signature = $rsa->sign('1234567890', Zend_Crypt_Rsa::BASE64);
        $this->assertEquals(
        'sMHpp3u6DNecIm5RIkDD3xyKaH6qqP8roUWDs215iOGHehfK1ypqwoETKNP7NaksGS2C1Up813ixlGXkipPVbQ==',
        $signature);
    }

    public function testVerifyVerifiesBinarySignatures()
    {
        $rsa = new Zend_Crypt_Rsa(array('pemString'=>$this->_testPemString));
        $signature = $rsa->sign('1234567890');
        $result = $rsa->verifySignature('1234567890', $signature);
        $this->assertEquals(1, $result);
    }

    public function testVerifyVerifiesBase64Signatures()
    {
        $rsa = new Zend_Crypt_Rsa(array('pemString'=>$this->_testPemString));
        $signature = $rsa->sign('1234567890', Zend_Crypt_Rsa::BASE64);
        $result = $rsa->verifySignature('1234567890', $signature, Zend_Crypt_Rsa::BASE64);
        $this->assertEquals(1, $result);
    }

    public function testEncryptionUsingPublicKeyEncryption()
    {
        $rsa = new Zend_Crypt_Rsa(array('pemString'=>$this->_testPemString));
        $encrypted = $rsa->encrypt('1234567890', $rsa->getPublicKey());
        $this->assertEquals(
            '1234567890',
            $rsa->decrypt($encrypted, $rsa->getPrivateKey())
        );
    }

    public function testEncryptionUsingPublicKeyBase64Encryption()
    {
        $rsa = new Zend_Crypt_Rsa(array('pemString'=>$this->_testPemString));
        $encrypted = $rsa->encrypt('1234567890', $rsa->getPublicKey(), Zend_Crypt_Rsa::BASE64);
        $this->assertEquals(
            '1234567890',
            $rsa->decrypt($encrypted, $rsa->getPrivateKey(), Zend_Crypt_Rsa::BASE64)
        );
    }

    public function testEncryptionUsingPrivateKeyEncryption()
    {
        $rsa = new Zend_Crypt_Rsa(array('pemString'=>$this->_testPemString));
        $encrypted = $rsa->encrypt('1234567890', $rsa->getPrivateKey());
        $this->assertEquals(
            '1234567890',
            $rsa->decrypt($encrypted, $rsa->getPublicKey())
        );
    }

    public function testEncryptionUsingPrivateKeyBase64Encryption()
    {
        $rsa = new Zend_Crypt_Rsa(array('pemString'=>$this->_testPemString));
        $encrypted = $rsa->encrypt('1234567890', $rsa->getPrivateKey(), Zend_Crypt_Rsa::BASE64);
        $this->assertEquals(
            '1234567890',
            $rsa->decrypt($encrypted, $rsa->getPublicKey(), Zend_Crypt_Rsa::BASE64)
        );
    }

    public function testKeyGenerationCreatesArrayObjectResult() 
    {
        $rsa = new Zend_Crypt_Rsa;
        $keys = $rsa->generateKeys(array('private_key_bits'=>512));
        $this->assertType('ArrayObject', $keys);
    }

    public function testKeyGenerationCreatesDualPrivateKeyInArrayObject() 
    {
        $rsa = new Zend_Crypt_Rsa;
        $keys = $rsa->generateKeys(array('private_key_bits'=>512));
        $this->assertType('Zend_Crypt_Rsa_Key_Private', $keys->privateKey);
    }

    public function testKeyGenerationCreatesDualPublicKeyInArrayObject() 
    {
        $rsa = new Zend_Crypt_Rsa;
        $keys = $rsa->generateKeys(array('private_key_bits'=>512));
        $this->assertType('Zend_Crypt_Rsa_Key_Public', $keys->publicKey);
    }

}