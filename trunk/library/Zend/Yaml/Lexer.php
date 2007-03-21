<?php

class Zend_Yaml_Lexer
{

    private $_done = false;
    private $_flowLevel = 0;
    private $_tokens = array();
    private $_tokensTaken = 0;
    private $_indent = -1;
    private $_indents = array();
    private $_allowSimpleKey = true;
    private $_possibleSimpleKeys = array();

    public function __construct()
	{
        $this->_fetchStreamStart();
    }

    public function checkToken(array $choices)
	{
        while ($this->_needMoreTokens()) {
            $this->_fetchMoreTokens();
        }
        if ($this->_tokens) {
            if (count($choices) == 0) {
                return true;
            }
            foreach ($choices as $choice) {
                if (!is_object($choice)) {
                    continue;
                }
                if ($this->_tokens[0] instanceof get_class($choice)) {
                    return true;
                }
            }
        }
        return false;
    }

    public function peekToken()
	{
        while ($this->_needMoreTokens()) {
            $this->_fetchMoreTokens();
        }
        if ($this->_tokens) {
            return $this->_tokens[0];
        }
    }

    public function getToken()
	{
        while ($this->_needMoreTokens()) {
            $this->_fetchMoreTokens();
        }
        if (!empty($this->_tokens)) {
            $this->_tokensTaken += 1;
            return array_shift($this->_tokens);
        }
    }

    private function _peek()
	{

    }

    private function _prefix()
	{

    }

    private function _prefixForward()
	{

    }

    private function _forward()
	{

    }

    private function _checkPrintable()
	{

    }

    private function _update()
	{

    }

    private function _needMoreTokens()
	{

    }

    private function _fetchMoreTokens()
	{

    }

    private function _nextPossibleSimpleKey()
	{

    }
    
    private function _savePossibleSimpleKey()
	{

    }
    
    private function _unwindIndent()
	{

    }
    
    private function _addIndent()
	{

    }

    private function _fetchStreamStart()
	{

    }

    private function _fetchStreamEnd()
	{

    }

    private function _fetchDirective()
	{

    }
    
    private function _fetchDocumentStart()
	{

    }

    private function _fetchDocumentEnd()
	{

    }

    private function _fetchDocumentIndicator()
	{

    }
    
    private function _fetchFlowSequenceStart()
	{

    }

    private function _fetchFlowMappingStart()
	{

    }

    private function _fetchFlowCollectionStart()
	{

    }

    private function _fetchFlowSequenceEnd()
	{

    }
    
    private function _fetchFlowMappingEnd()
	{

    }
    
    private function _fetchFlowCollectionEnd()
	{

    }
    
    private function _fetchFlowEntry()
	{

    }

    private function _fetchBlockEntry()
	{

    }        

    private function _fetchKey()
	{

    }

    private function _fetchValue()
	{

    }

    private function _fetchAlias()
	{

    }

    private function _fetchAnchor()
	{

    }

    private function _fetchTag()
	{

    }
    
    private function _fetchLiteral()
	{

    }
    
    private function _fetchFolded()
	{

    }
    
    private function _fetchBlockScalar()
	{

    }
    
    private function _fetchSingle()
	{

    }
    
    private function _fetchDouble()
	{

    }
    
    private function _fetchFlowScalar()
	{

    }
    
    private function _fetchPlain()
	{

    }
    
    private function _scanToNextToken()
	{

    }
    
    private function _scanDirective()
	{

    }
    
    private function _scanDirectiveName()
	{

    }

    private function _scanYamlDirectiveValue()
	{

    }

    private function _scanYamlDirectiveNumber()
	{

    }

    private function _scanTagDirectiveValue() ()
	{

    }

    private function _scanTagDirectiveHandle()
	{

    }
    
    private function _scanTagDirectivePrefix()
	{

    }

    private function _scanDirectiveIgnoredLine()
	{

    }

    private function _scanAnchor()
	{

    }

    private function _scanTag()
	{

    }

    private function _scanBlockScalar()
	{

    }

    private function _scanBlockScalarIndicators()
	{

    }

    private function _scanBlockScalarIgnoredLine()
	{

    }

    private function _scanBlockScalarIndentation()
	{

    }

    private function _scanBlockScalarBreaks()
	{

    }

    private function _scanFlowScalar()
	{

    }

    private function _scanFlowScalarNonSpaces()
	{

    }

    private function _scanFlowScalarSpaces()
	{

    }

    private function _scanFlowScalarBreaks()
	{

    }


    private function _scanPlain()
	{

    }

    private function _scanPlainSpaces()
	{

    }

    private function _scanTagHandle()
	{

    }

    private function _scanTagUri()
	{

    }

    private function _scanUriEscapes()
	{

    }

    private function _scanLineBreak()
	{

    }

}