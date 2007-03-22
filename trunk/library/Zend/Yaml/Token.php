<?php

class Zend_Yaml_Token 
{
    protected $_id = null;

    protected $_isDocumentStart = false;
    protected $_isDocumentE= false;
    protected $_isStreamStart = false;
    protected $_isStreamEnd = false;
    protected $_isDirective = false;
    protected $_isBlockSequenceStart = false;
    protected $_isBlockMappingStart = false;
    protected $_isBlockEnd = false;
    protected $_isFlowSequenceStart = false;
    protected $_isFlowMappingStart = false;
    protected $_isFlowSequenceEnd = false;
    protected $_isFlowMappingEnd = false;
    protected $_isKey = false;
    protected $_isValue = false;
    protected $_isBlockEntry = false;
    protected $_isFlowEntry = false;
    protected $_isAlias = false;
    protected $_isAnchor = false;
    protected $_isTag = false;
    protected $_isScalar = false;

    public function __construct()
    {
        require_once 'Zend/Yaml/Exception.php';
        throw new Zend_Yaml_Exception('Zend_Yaml_Token should not be instantiated, please use one of the concrete Token subclasses');
    }

    public function getId()
    {
        return $this->_id;
    }

    public function isDocumentStart() { return $this->_isDocumentStart; }
    public function isDocumentEnd() { return $this->_isDocumentEnd; }
    public function isStreamStart() { return $this->_isStreamStart; }
    public function isStreamEnd() { return $this->_isStreamEnd; }
    public function isDirective() { return $this->_isDirective; }
    public function isBlockSequenceStart() { return $this->_isBlockSequenceStart; }
    public function isBlockMappingStart() { return $this->_isBlockMappingStart; }
    public function isBlockEnd() { return $this->_isBlockEnd; }
    public function isFlowSequenceStart() { return $this->_isFlowSequenceStart; }
    public function isFlowMappingStart() { return $this->_isFlowMappingStart; }
    public function isFlowSequenceEnd() { return $this->_isFlowSequenceEnd; }
    public function isFlowMappingEnd() { return $this->_isFlowMappingEnd; }
    public function isKey() { return $this->_isKey; }
    public function isValue() { return $this->_isValue; }
    public function isBlockEntry() { return $this->_isBlockEntry; }
    public function isFlowEntry() { return $this->_isFlowEntry; }
    public function isAlias() { return $this->_isAlias; }
    public function isAnchor() { return $this->_isAnchor; }
    public function isTag() { return $this->_isTag; }
    public function isScalar() { return $this->_isScalar; }

}