<?php

/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Crypt
 * @copyright  Copyright (c) 2007 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Validate.php 4974 2007-05-25 21:11:56Z bkarwin $
 */

require_once 'Zend/Math/BigInteger.php';

class Zend_Crypt_Math_BigInteger extends Zend_Math_BigInteger
{
    
    /**
     * Generate a pseudorandom number within the given range.
     *
     * @param string|int $min
     * @param string|int $max
     * @return string
     */
    public function rand($min, $max)
    {
        if($this->_math->compare($max,$min)!=1){
            return 0;
        }
        $top = $this->_math->subtract($max,$min);
        $rand = $this->_math->add($top, 1);
        $length = strlen($top);
        $n = 0;
        while(9*$n <= $length){
            if($length - 9*$n >= 9){
                $rand_part[] = mt_rand(0,999999999);
            }else{
                $j = 0; $foo = '';
                while($j < $length-9*$n){
                    $foo .= '9';
                    ++$j;
                }
                $foo += 0;
                $rand_part[] = mt_rand(0,$foo);
            }
            ++$n;
        }
        $i = 0;
        $rand ='';
        $count = count($rand_part);
        while($i < $count){
            $rand .= $rand_part[$i];
            ++$i;
        }
        while(bccomp($rand,$top)==1){
            $rand = substr($rand,1,strlen($rand)).rand(0,9);
        }
        return bcadd($rand,$min);
    }

}