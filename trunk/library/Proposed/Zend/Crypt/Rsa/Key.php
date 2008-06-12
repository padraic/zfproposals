<?php

class Zend_Crypt_Rsa_Key implements Countable
{
    
    protected $_pemString = null;

    protected $_details = array();

    protected $_opensslKeyResource = null;

    public function getOpensslKeyResource() 
    {
        return $this->_opensslKeyResource;
    }

    public function toString() 
    {
        return $this->_pemString;
    }

    public function __toString() 
    {
        return $this->toString();
    }

    public function count() 
    {
        return $this->_details['bits'];
    }

    public function getType() 
    {
        return $this->_details['type'];
    }
}