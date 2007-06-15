<?php

require_once 'Zend/Crypt/DiffieHellman.php';
require_once 'PHPUnit/Framework/TestCase.php';

class Zend_Crypt_DiffieHellmanTest extends PHPUnit_Framework_TestCase 
{

    private $_diffie = null;

    public function setUp()
    {
        $this->_diffie = new Zend_Crypt_DiffieHellman;
    }

    public function testDiffieWithSpec()
    {
        $aliceOptions = array(
            'prime'=>'563',
            'generator'=>'5',
            'private'=>'9'
        );
        $bobOptions = array(
            'prime'=>'563',
            'generator'=>'5',
            'private'=>'14'
        );
        $alice = new Zend_Crypt_DiffieHellman($aliceOptions);
        $bob = new Zend_Crypt_DiffieHellman($bobOptions);
        $alice->generateKeys();
        $bob->generateKeys();

        $this->assertEquals('78', $alice->getPublicKey());
        $this->assertEquals('534', $bob->getPublicKey());
        
        $aliceSecretKey = $alice->computeSecretKey($bob->getPublicKey());
        $bobSecretKey = $bob->computeSecretKey($alice->getPublicKey());
        
        // both Alice and Bob should now have the same secret key
        $this->assertEquals('117', $aliceSecretKey);
        $this->assertEquals('117', $bobSecretKey);
    }

    public function testDiffieWithDefaults()
    {
        $alice = new Zend_Crypt_DiffieHellman;
        $bob = new Zend_Crypt_DiffieHellman;
        $alice->generateKeys();
        $bob->generateKeys();
        
        $aliceSecretKey = $alice->computeSecretKey($bob->getPublicKey());
        $bobSecretKey = $bob->computeSecretKey($alice->getPublicKey());
        
        // both Alice and Bob should now have the same secret key
        $this->assertEquals($aliceSecretKey, $bobSecretKey);
    }

}