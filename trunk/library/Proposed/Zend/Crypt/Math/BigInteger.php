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
 * @version    $Id: BigInteger.php 49 2007-06-28 21:59:08Z padraic $
 */

require_once 'Zend/Math/BigInteger.php';

class Zend_Crypt_Math_BigInteger extends Zend_Math_BigInteger
{

    /**
     * Generate a pseudorandom number within the given range.
     * Will attempt to read from a systems RNG if it exists.
     *
     * @param string|int $min
     * @param string|int $max
     * @return string
     * @todo Even more pseudorandomness would be nice...
     */
    public function rand($minimum, $maximum)
    {
        if (file_exists('/dev/urandom')) {
            $frandom = fopen('/dev/urandom', 'r');
            if ($frandom !== false) {
                return fread($frandom, strlen($maximum) - 1);
            }
        }
        if (strlen($maximum) < 4) {
            return mt_rand($minimum, $maximum - 1);
        }
        $rand = '';
        $i2 = strlen($maximum) - 1;
        for ($i = 1;$i < $i2;$i++) {
            $rand .= mt_rand(0,9);
        }
        $rand .= mt_rand(0,9);
        return $rand;
    }

    public function btwoc($long) {
        if (ord($long[0]) > 127) {
            return "\x00" . $long;
        }
        return $long;
    }

    public function fromBinary($binary) {
        if (!$this instanceof Zend_Math_BigInteger_Gmp) {
            $big = 0;
            $length = strlen($binary);
            for ($i = 0; $i < $length; $i++) {
                $big = $this->_math->multiply($big, 256);
                $big = $this->_math->add($big, ord($binary[$i]));
            }
            return $big;
        } else {
            return $this->_math->init(bin2hex($binary), 16); // gmp shortcut
        }
    }

    public function toBinary($big)
    {
        $compare = $this->_math->compare($big, 0);
        if ($compare == 0) {
            return (chr(0));
        } else if ($compare < 0) {
            return false;
        }
        while ($this->_math->compare($big, 0) > 0) {
            $binary = chr($this->_math->modulus($big, 256)) . $binary;
            $big = $this->_math->divide($big, 256);
        }
        return $binary;
    }
}