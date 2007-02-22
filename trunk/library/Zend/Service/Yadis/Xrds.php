<?php

class Zend_Service_Yadis_ResultSet implements SeekableIterator
{

    /**
     * The Yadis Services resultset
     *
     * @var array
     */ 
    protected $_services;
 
    /**
     * Current key/pointer for the Iterator
     * 
     * @var integer
     */ 
    private $_currentKey = 0;
 
    /**
     * Parse the XRD document into a set of Service objects.
     * 
     * @param SimpleXMLElement $xrdsDocument
     */ 
    public function __construct(SimpleXMLElement $xrdsDocument)
    {
        echo $xrdsDocument; exit;

        /*
         * Register all namespaces as set previously for the service.
         * Need these or else our xpath queries will fail ;).
         */
        foreach($this->_namespaces as $namespace=>$namespaceUrl)
        {
            $xrds->registerXPathNamespace($namespace, $namespaceUrl);
        }
        
        /*
         * Verify the XRDS resource has a root element called "xrds:XRDS".
         */
        $root = $xrds->xpath('/xrds:XRDS[1]');
        if(count($root) == 0)
        {
            return null;
        }

        /*
         * Check namespace urls of standard xmlns (no suffix) or xmlns:xrd
         * (if present and of priority) for validity.
         * No loss if neither exists, but they really should be.
         */
        $nameSpaces = $xrds->getDocNamespaces();
        if(array_key_exists('xrd', $nameSpaces) && $nameSpaces['xrd'] != 'xri://$xrd*($v*2.0)')
        {
            return null;
        }
        elseif(array_key_exists('', $nameSpaces) && $nameSpaces[''] != 'xri://$xrd*($v*2.0)')
        {
            return null;
        }

        /*
         * Grab the XRD element which contains details of the service provider's
         * Server url, service types, and other details.
         */
        $xrdNode = $xml->XRD[1];
        
        }
 
    /**
     * Implements SeekableIterator::current()
     * 
     * Return the current element.
     *
     * @return Zend_Service_Yadis_Service
     */ 
    public function current()
    {}
 
    /**
     * Implements SeekableIterator::key()
     *
     * Return the key of the current element.
     * 
     * @return integer
     */ 
    public function key()
    {}
 
    /**
     * Implements SeekableIterator::next()
     * 
     * Increments pointer to next Service object.
     */ 
    public function next()
    {}
 
    /**
     * Implements SeekableIterator::rewind()
     * 
     * Rewinds the Iterator to the first Service object
     *
     * @return boolean
     */ 
    public function rewind()
    {}
 
    /**
     * Implements SeekableIterator::seek()
     * 
     * Seek to an absolute position.
     *
     * @param  integer $key
     * @return Zend_Service_Yadis_Service
     * @throws Zend_Service_Yadis_Exception
     */ 
    public function seek($key)
    {}
 
    /**
     * Implement SeekableIterator::valid()
     *
     * @param  integer $key
     * @return boolean
     */ 
    public function valid($key = null)
    {}

}