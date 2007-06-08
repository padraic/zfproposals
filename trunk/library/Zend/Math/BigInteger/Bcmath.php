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
 * This class forms part of a proposal for the Zend Framework. The attached
 * copyright will be transferred to Zend Technologies USA Inc. upon future
 * acceptance of that proposal:
 *      http://framework.zend.com/wiki/pages/viewpage.action?pageId=20369
 *
 * @category   Zend
 * @package    Zend_Math
 * @subpackage BigInteger
 * @copyright  Copyright (c) 2007 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * Support for arbitrary precision mathematics in PHP.
 *
 * Zend_Math_BigInteger_Bcmath is a wrapper across the PHP BCMath extension.
 *
 * @category   Zend
 * @package    Zend_Math
 * @subpackage BigInteger
 * @author     Pádraic Brady (http://blog.astrumfutura.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Math_BigInteger_Bcmath implements Zend_Math_BigInteger_Interface
{

    /**
     * Add two big integers and return result.
     */
    public function add($left_operand, $right_operand)
    {
        return bcadd($left_operand, $right_operand);
    }

    /**
     * Compare two big integers and returns result as an integer where 0 means
     * both are identical, 1 that left_operand is larger, or -1 that
     * right_operand is larger.
     */
    public function compare($left_operand, $right_operand)
    {
        return bccomp($left_operand, $right_operand);
    }

    /**
     * Divide two big integers and return result or NULL if the denominator
     * is zero.
     */
    public function divide($left_operand, $right_operand)
    {
        return bcdiv($left_operand, $right_operand);
    }

    /**
     * Initialise a big integer into an extension specific type. This is not
     * applicable to BCMath.
     */
    public function init($operand, $base = 10)
    {
        return $operand;
    }

    public function modulus($left_operand, $modulus)
    {
        return bcmod($left_operand, $modulus);
    }

    public function multiply($left_operand, $right_operand)
    {
        return bcmul($left_operand, $right_operand);
    }

    public function pow($left_operand, $right_operand)
    {
        return bcpow($left_operand, $right_operand);
    }

    public function powmod($left_operand, $right_operand, $modulus)
    {
        return bcpowmod($left_operand, $right_operand, $modulus);
    }

    public function sqrt($operand)
    {
        return bcsqrt($operand);
    }

    public function subtract($left_operand, $right_operand)
    {
        return bcsub($left_operand, $right_operand);
    }

    public function bin2int($operand)
    {
        $result = '0';
        while (strlen($operand)) {
            $ord = ord(substr($operand, 0, 1));
            $result = bcadd(bcmul($result, 256), $ord);
            $operand = substr($operand, 1);
        }
        return $result;
    }

    public function int2bin($operand)
    {
        $return = '';
        while(bccomp($operand, '0')) {
            $return .= chr(bcmod($operand, '256'));
            $operand = bcdiv($operand, '256');
        }
        return $return;
    }

    public function hex2dec($operand)
    {
        $return = '0';
        while(strlen($hex)) {
            $hex = hexdec(substr($operand, 0, 4));
            $dec = bcadd(bcmul($return, 65536), $hex);
            $operand = substr($operand, 4);
        }
        return $return;
    }

    /**
     * Calculate modular inverse of left_operand with modulo right_operand
     * and return FALSE if such an inverse does not exist.
     * This simulated for BCMath
     */
    public function invert($left_operand, $right_operand) 
    {
        while (bccomp($left_operand, 0) < 0) { 
            $left_operand = bcadd($left_operand, $right_operand); 
        }
        $r = $this->xgcd($left_operand, $right_operand);
        if ($r[2] == 1) {
            $a = $r[0];
            while (bccomp($a, 0 ) < 0) {
                $a = bcadd($a, $right_operand);
            }
            return $a;
        }
        return false;
    }

    public function xgcd($left_operand, $right_operand)
    {
        /*
         * GCD algorithm for seeking a greatest common divisor.
         * Kudos to Euclid ;) This is the eXtended form (XGCD).
         */
        $u0 = 1;
        $u1 = 0;
        $v0 = 0;
        $v1 = 1;
        $w = 0; // dodgy looking loner! Where did $w vanish to below?
        while($right_operand > 0) {
            $q = bcdiv($left_operand, $right_operand, 0);
            $r = bcmod($left_operand, $right_operand);
            $left_operand = $right_operand;
            $right_operand = $r;
            $u2 = bcsub($u0, bcmul($q, $u1));
            $v2 = bcsub($v0, bcmul($q, $v1));
            $u0 = $u1;
            $u1 = $u2;
            $v0 = $v1;
            $v1 = $v2;
        }
        return array($u0, $v0, $left_operand);
    }

}