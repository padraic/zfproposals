<?php

require_once 'Zend/Crypt/Math/BigInteger.php';
require_once 'PHPUnit/Framework/TestCase.php';

class Zend_Crypt_Math_BigIntegerTest extends PHPUnit_Framework_TestCase 
{

    public function testRand()
    {
        $math = new Zend_Crypt_Math_BigInteger;
        $higher = '155172898181473697471232257763715539915724801966915404479707795314057629378541917580651227423698188993727816152646631438561595825688188889951272158842675419950341258706556549803580104870537681476726513255747040765857479291291572334510643245094715007229621094194349783925984760375594985848253359305585439638443';
        $lower1 = $higher;
        
        $result = $math->rand($lower1, $higher);
        $this->assertEquals(0, $result);

        // running check on whether max/min bounds are ever broken
        $lower2  = '155172898181473697471232257763715539915724801966915404479707795314057629378541917580651227423698188993727816152646631438561595825688188889951272158842675419950341258706556549803580104870537681476726513255747040765857479291291572334510643245094715007229621094194349783925984760375594985848253359305585439638442';
        $result = $math->rand($lower2, $higher);
        $this->assertTrue(bccomp($result, $higher) !== '1');
        $this->assertTrue(bccomp($result, $lower2) !== '-1');
    }

}