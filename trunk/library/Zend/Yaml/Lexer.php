<?php

/** Zend_Yaml_Token */
require_once 'Zend/Yaml/Token.php';

/** Zend_Yaml_SimpleKey */
require_once 'Zend/Yaml/SimpleKey.php';

/** Zend_Yaml_Constants */
require_once 'Zend/Yaml/Constants.php'

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

    private function _needMoreTokens()
	{
        if ($this->_done) return false;
        if(empty($this->_tokens)) {
            return true;
        }
        // removeStalePossibleSimpleKeys
        if($this->_nextPossibleSimpleKey() == $this->_tokensTaken) {
            return true;
        }
        return false;
    }


    private function _fetchMoreTokens()
	{
        $this->_scanToNextToken();
        // removeStalePossibleSimpleKeys
        $this->_unwindIndent($this->_column);
        $chr = $this->_peek();
        switch ($chr) {
            case "\0":
                return $this->_fetchStreamEnd();
                break;
            case "%":
                if ($this->_checkDirective()) {
                    return $this->_fetchDirective();
                }
                break;
            case "-":
                if ($this->_checkDocumentStart()) {
                    return $this->_fetchDocumentStart();
                }
                break;
            case ".":
                if ($this->_checkDocumentEnd()) {
                    return $this->_fetchDocumentEnd();
                }
                break;
            case "[":
                return $this->_fetchFlowSequenceStart();
                break;
            case "{":
                return $this->_fetchFlowMappingStart();
                break;
            case "]":
                return $this->_fetchFlowSequenceEnd();
                break;
            case "}":
                return $this->_fetchFlowMappingEnd();
                break;
            case ",":
                return $this->_fetchFlowEntry();
                break;
            case "-":
                if ($this->_checkBlockEntry()) {
                    return $this->_fetchBlockEntry();
                }
                break;
            case "?":
                if ($this->_checkKey()) {
                    return $this->_fetchKey();
                }
                break;
            case ":":
                if ($this->_checkValue()) {
                    return $this->_fetchValue();
                }
                break;
            case "*":
                return $this->_fetchAlias();
                break;
            case "&":
                return $this->_fetchAnchor();
                break;
            case "!":
                return $this->_fetchTag();
                break;
            case "|":
                if (empty($this->_flowLevel)) {
                    return $this->_fetchLiteral();
                }
                break;
            case ">":
                if (empty($this->_flowLevel)) {
                    return $this->_fetchFolded();
                }   
                break;
            case "'":
                return $this->_fetchSingle();
                break;
            case "\"":
                return $this->_fetchDouble();
                break;
            default:
                if ($this->_checkPlain()) {
                    return $this->_fetchPlain();
                }
        }
        require_once 'Zend/Yaml/Exception.php';
        throw new Zend_Yaml_Exception('Scanning for next token but found character: ' . $chr . ' which is not a valid token start mark');
    }

    private function _nextPossibleSimpleKey()
	{
        foreach ($this->_possibleSimpleKeys as $key) {
            if ($key->tokenNumber > 0) {
                return $key->tokenNumber;
            }
        }
        return null;
    }
    
    private function _savePossibleSimpleKey()
	{
        $req = false;
        if (empty($this->_flowLevel) && $this->_indent == $this->_column) {
            $req = true;
        }
        if ($this->_allowSimpleKey) {
            $this->_possibleSimpleKeys[$this->_flowLevel] = new Zend_Yaml_SimpleKey($this->_tokenNumber, $req, $this->_column);
        }
    }
    
    private function _unwindIndent($column)
	{
        if (empty($this->_flowLevel)) {
            return;
        }
        while ($this->_indent > $column) {
            $indent = array_pop($this->_indents);
            $this->_tokens[] = new Zend_Yaml_Token_BlockEnd;
        }
    }
    
    private function _addIndent($column)
	{
        if ($this->_indent < $column) {
            $this->_indents[] = $this->_indent;
            $this->_indent = $column;
            return true;
        }
        return false;
    }

    private function _fetchStreamStart()
	{
        $this->_tokens[] = new Zend_Yaml_Token_StreamStart;
    }

    private function _fetchStreamEnd()
	{
        $this->_unwindIndent(-1);
        $this->_allowSimpleKey = false;
        $this->_possibleSimpleKeys = array();
        $this->_tokens[] = new Zend_Yaml_Token_StreamEnd;
        $this->_done = true;
    }

    private function _fetchDirective()
	{
        $this->_unwindIndent(-1);
        $this->_allowSimpleKey = false;
        $this->_tokens[] = $this->_scanDirective();
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
        $this->_forward();
        $name = $this->_scanDirectiveName();
        $value = null;
        if ($name == 'YAML') {
            $value = $this->_scanYamlDirectiveValue();
        } elseif ($name == 'TAG') {
            $value = $this->_scanTagDirectiveValue();
        } else {
            while ($this->_peek()) {
                $this->_forward();
            }
        }
        $this->_scanDirectiveIgnoredLine();
        $temp = new Zend_Yaml_Token_Directive($name, $value);
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


    /*private function _removePossibleSimpleKey()
    {
        if (isset($this->_possibleSimpleKeys[$this->_flowLevel])) {
            if ($this->_possibleSimpleKeys[$this->_flowLevel]->required) {
                require_once 'Zend/Yaml/Exception.php';
                throw new Zend_Yaml_Exception('Unable to find an expected ":" simple key');
            }
            $this->_possibleSimpleKeys[$this->_flowLevel] = null;
        }
    }*/

}