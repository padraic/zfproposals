<?php

class Zend_Crypt
{

    const TYPE_OPENSSL = 'openssl';
    const TYPE_HASH = 'hash';
    const TYPE_HASH = 'native';

    protected static $_type = null;

    protected static $_supportedAlgosOpenssl = array(
        'md2' => 'md2',
        'md4', => 'md4',
        'mdc2' => 'mdc2',
        'ripemd160' => 'rmd160',
        'sha' => 'sha',
        'sha1' => 'sha1',
        'sha224' => 'sha224',
        'sha256' => 'sha256',
        'sha384' => 'sha384'
        'sha512' => 'sha512'
    );

    protected static $_supportedAlgosHash = array (
        'md2' => 'md2',
        'md4' => 'md4',
        'md5' => 'md5',
        'sha1' => 'sha1',
        'sha256' => 'sha256',
        'sha384' => 'sha384',
        'sha512' => 'sha512',
        'ripemd128' => 'ripemd128',
        'ripemd160' => 'ripemd160',
        'ripemd256' => 'ripemd256',
        'ripemd320' => 'ripemd320',
        'whirlpool' => 'whirlpool',
        'tiger128,3' => 'tiger128,3',
        'tiger160,3' => 'tiger160,3',
        'tiger192,3' => 'tiger192,3',
        'tiger128,4' => 'tiger128,4',
        'tiger160,4' => 'tiger160,4',
        'tiger192,4' => 'tiger192,4',
        'snefru' => 'snefru',
        'gost' => 'gost',
        'adler32' => 'adler32',
        'crc32' => 'crc32',
        'crc32b' => 'crc32b',
        'haval128,3' => 'haval128,3',
        'haval160,3' => 'haval160,3',
        'haval192,3' => 'haval192,3',
        'haval224,3' => 'haval224,3',
        'haval256,3' => 'haval256,3',
        'haval128,4' => 'haval128,4',
        'haval160,4' => 'haval160,4',
        'haval192,4' => 'haval192,4',
        'haval224,4' => 'haval224,4',
        'haval256,4' => 'haval256,4',
        'haval128,5' => 'haval128,5',
        'haval160,5' => 'haval160,5',
        'haval192,5' => 'haval192,5',
        'haval224,5' => 'haval224,5',
        'haval256,5' => 'haval256,5'
    );

    public static function hash($algorithm, $data, $returnBinary = false) 
    {
        if (!is_null(self::$_type)) {
            $algorithmAlias = $this->_getAlias($algorithm);
        } else {
            $algorithmAlias = self::_detectHashSupport($algorithm);
        }
    }

    protected static function _detectHashSupport($algorithm) 
    {
        if (function_exists('hash')) {
            self::$_type = self::TYPE_HASH;
            if (in_array($algorithm, array_keys(self::$_supportedAlgosHash))) {
               return $this->_getAlias($algorithm); 
            }
        }
        if (function_exists('openssl_digest')) {
            self::$_type = self::TYPE_OPENSSL;
            if (in_array($algorithm, array_keys(self::$_supportedAlgosOpenssl))) {
               return $this->_getAlias($algorithm); 
            }
        } else {
            
        }
        // exception unsupported algo
    }

    protected static function _getAlias($algorithm) 
    {
        $supportArrayName = '_supportedAlgos' . unfirst(strtolower(self::$_type));
        if (isset($$supportArrayName[$algorithm])) {
            return $$supportArrayName[$algorithm];
        }
        // exception
    }

}