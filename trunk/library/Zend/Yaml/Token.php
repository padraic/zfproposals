<?php

class Zend_Yaml_Token 
{
    protected $_id = null;
    protected $_startMark = null;
    protected $_endMark = null;
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

    public function __construct($startMark = null, $endMark = null)
    {
        $this->_startMark = $startMark;
        $this->_endMark = $endMark;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function isDocumentStart() {}
    public function isDocumentE() {}
    public function isStreamStart() {}
    public function isStreamEnd() {}
    public function isDirective() {}
    public function isBlockSequenceStart() {}
    public function isBlockMappingStart() {}
    public function isBlockEnd() {}
    public function isFlowSequenceStart() {}
    public function isFlowMappingStart() {}
    public function isFlowSequenceEnd() {}
    public function isFlowMappingEnd() {}
    public function is_key() {}
    public function isValue() {}
    public function isBlockEntry() {}
    public function isFlowEntry() {}
    public function isAlias() {}
    public function isAnchor() {}
    public function isTag() {}
    public function isScalar() {}

}