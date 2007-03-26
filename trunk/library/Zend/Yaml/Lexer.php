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
 * @package    Zend_Yaml
 * @copyright  Copyright (c) 2007 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @todo    _scan level methods and regex checking
 */

/** Zend_Yaml_Token */
require_once 'Zend/Yaml/Token.php';
require_once 'Zend/Yaml/Token/Alias.php';
require_once 'Zend/Yaml/Token/Anchor.php';
require_once 'Zend/Yaml/Token/BlockEnd.php';
require_once 'Zend/Yaml/Token/BlockMappingStart.php';
require_once 'Zend/Yaml/Token/BlockSequenceStart.php';
require_once 'Zend/Yaml/Token/BlockStreamingEnd.php';
require_once 'Zend/Yaml/Token/Directive.php';
require_once 'Zend/Yaml/Token/DocumentStart.php';
require_once 'Zend/Yaml/Token/DocumentEnd.php';
require_once 'Zend/Yaml/Token/FlowEntry.php';
require_once 'Zend/Yaml/Token/FlowMappingStart.php';
require_once 'Zend/Yaml/Token/FlowMappingEnd.php';
require_once 'Zend/Yaml/Token/FlowSequenceStart.php';
require_once 'Zend/Yaml/Token/FlowSequenceEnd.php';
require_once 'Zend/Yaml/Token/Key.php';
require_once 'Zend/Yaml/Token/Scalar.php';
require_once 'Zend/Yaml/Token/StreamStart.php';
require_once 'Zend/Yaml/Token/StreamEnd.php';
require_once 'Zend/Yaml/Token/Tag.php';
require_once 'Zend/Yaml/Token/Value.php';

/** Zend_Yaml_SimpleKey */
require_once 'Zend/Yaml/SimpleKey.php';

/** Zend_Yaml_Constants */
require_once 'Zend/Yaml/Constants.php'

/**
 * Zend_Yaml_Lexer is a YAML string scanner which produces lexical tokens based
 * on the YAML 1.1 Specification. The string being scanned is sourced from
 * Zend_Yaml_Buffer which encapsulates the String or File Stream to be scanned.
 *
 * @category   Zend
 * @package    Zend_Yaml
 * @author     Pádraic Brady (http://blog.astrumfutura.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
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

    private $_buffer = null

    public function __construct(Zend_Yaml_Buffer $buffer)
	{
        $this->_buffer = $buffer;
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
        if($this->_nextPossibleSimpleKey() == $this->_tokensTaken) {
            return true;
        }
        return false;
    }


    private function _fetchMoreTokens()
	{
        $this->_scanToNextToken();
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

    /**
     * Fetching methods   *****************************************************
     */

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
        $this->_fetchDocumentIndicator(new Zend_Yaml_Token_DocumentStart);
    }

    private function _fetchDocumentEnd()
	{
        $this->_fetchDocumentIndicator(new Zend_Yaml_Token_DocumentEnd);
    }

    private function _fetchDocumentIndicator(Zend_Yaml_Token $token)
	{
        $this->_unwindIndent(-1);
        $this->_allowSimpleKey = false;
        $this->forward(3);
        $this->_tokens[] = $token;
    }
    
    private function _fetchFlowSequenceStart()
	{
        $this->_fetchFlowCollectionStart(new Zend_Yaml_Token_FlowSequenceStart);
    }

    private function _fetchFlowMappingStart()
	{
        $this->_fetchFlowCollectionStart(new Zend_Yaml_Token_FlowMappingStart);
    }

    private function _fetchFlowCollectionStart(Zend_Yaml_Token $token)
	{
        $this->_savePossibleSimpleKey();
        $this->_flowLevel += 1;
        $this->_allowSimpleKey = true;
        $this->_forward();
        $this->_tokens[] = $token;
    }

    private function _fetchFlowSequenceEnd()
	{
        $this->_fetchFlowCollectionEnd(new Zend_Yaml_Token_FlowSequenceEnd);
    }
    
    private function _fetchFlowMappingEnd()
	{
        $this->_fetchFlowCollectionEnd(new Zend_Yaml_Token_FlowMappingEnd);
    }
    
    private function _fetchFlowCollectionEnd(Zend_Yaml_Token $token)
	{
        $this->_flowLevel -= 1;
        $this->_allowSimpleKey = false;
        $this->_forward();
        $this->_tokens[] = $token;
    }
    
    private function _fetchFlowEntry()
	{
        $this->_allowSimpleKey = true;
        $this->_forward();
        $this->_tokens[] = new Zend_Yaml_Token_FlowEntry;
    }

    private function _fetchBlockEntry()
	{
        if (empty($this->_flowLevel)) {
            if (!$this->_allowSimpleKey) {
                require_once 'Zend/Yaml/Exception.php';
                throw new Zend_Yaml_Exception('Invalid location for sequences in YAML');
            }
            if ($this->_addIndent($this->_column)) {
                $this->_tokens[] = new Zend_Yaml_Token_BlockSequenceStart;
            }
        }
        $this->_allowSimpleKey = true;
        $this->_forward();
        $this->_tokens[] = new Zend_Yaml_Token_BlockEntry;
    }        

    private function _fetchKey()
	{
        if (empty($this->_flowLevel)) {
            require_once 'Zend/Yaml/Exception.php';
            throw new Zend_Yaml_Exception('Invalid location for mapping keys in YAML');
        }
        if ($this->_addIndent($this->_column)) {
            $this->_tokens[] = new Zend_Yaml_Token_BlockMappingStart;
        }
        $this->_allowSimpleKey = (bool) empty($this->_flowLevel);
        $this->_forward();
        $this->_tokens[] = new Zend_Yaml_Token_Key;
    }

    private function _fetchValue()
	{
        $key = $this->_possibleSimpleKeys[$this->_flowLevel];
        if ($key) {
            $this->_possibleSimpleKeys[$this->_flowLevel] = null;
            unset($this->_possibleSimpleKeys[$this->_flowLevel]);
            $insertToken = array(new Zend_Yaml_Token_Key);
            $this->_arrayInsert($this->_tokens, ($key->tokenNumber - $this->_tokensTaken), $insertToken);
            if (empty($this->_flowLevel) && $this->_addIndent($key->column)) {
                $insertToken = array(new Zend_Yaml_Token_BlockMappingStart);
                $this->_arrayInsert($this->_tokens, ($key->tokenNumber - $this->_tokensTaken), $insertToken);
            }
            $this->_allowSimpleKey = false
        } else {
            if (empty($this->_flowLevel) && !$this->_allowSimpleKey) {
                require_once 'Zend/Yaml/Exception.php';
                throw new Zend_Yaml_Exception('Invalid location for mapping values in YAML');
            }
            if (empty($this->_flowLevel) && $this->_addIndent($this->_column)) {
                $this->_tokens[] = new Zend_Yaml_Token_BlockMappingStart;
            }
            $this->_allowSimpleKey = (bool) empty($this->_flowLevel);
        }
        $this->_forward();
        $this->_tokens[] = new Zend_Yaml_Token_Value;
    }

    private function _fetchAlias()
	{
        $this->_savePossibleSimpleKey();
        $this->_allowSimpleKey = false;
        $this->_tokens[] = $this->_scanAnchor(new Zend_Yaml_Token_Alias);
    }

    private function _fetchAnchor()
	{
        $this->_savePossibleSimpleKey();
        $this->_allowSimpleKey = false;
        $this->_tokens[] = $this->_scanAnchor(new Zend_Yaml_Token_Anchor);
    }

    private function _fetchTag()
	{
        $this->_savePossibleSimpleKey();
        $this->_allowSimpleKey = false;
        $this->_tokens[] = $this->_scanTag();
    }
    
    private function _fetchLiteral()
	{
        $this->_fetchBlockScalar('|');
    }
    
    private function _fetchFolded()
	{
        $this->_fetchBlockScalar('>');
    }
    
    private function _fetchBlockScalar($style)
	{
        $this->_allowSimpleKey = true;
        $this->_tokens[] = $this->_scanBlockScalar($style);
    }
    
    private function _fetchSingle()
	{
        $this->_fetchFlowScalar('\'');
    }
    
    private function _fetchDouble()
	{
        $this->_fetchFlowScalar('"');
    }
    
    private function _fetchFlowScalar($style)
	{
        $this->_savePossibleSimpleKey();
        $this->_allowSimpleKey = false;
        $this->_tokens[] = $this->_scanFlowScalar($style);
    }
    
    private function _fetchPlain()
	{
        $this->_savePossibleSimpleKey();
        $this->_allowSimpleKey = false;
        $this->_tokens[] = $this->_scanPlain();
    }

    /**
     * Scanning methods *******************************************************
     */
    
    private function _scanToNextToken()
	{
        while (true) {
            while ($this->_peek() = chr(32)) {
                $this->_forward();
            }
            if ($this->_peek() == '#') {
                while (!preg_match(Zend_Yaml_Constants::NULL_LINEBR, $this->_peek())) {
                    $this->_forward();
                }
            }
            if ($this->_scanLineBreak()) {
                if (empty($this->_flowLevel)) {
                    $this->_allowSimpleKey = true;
                }
            } else {
                break;
            }
        }
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
            while (!preg_match(Zend_Yaml_Constants::NULL_LINEBR, $this->_peek())) {
                $this->_forward();
            }
        }
        $this->_scanDirectiveIgnoredLine();
        $return = new Zend_Yaml_Token_Directive($name, $value);
        return $return;
    }
    
    private function _scanDirectiveName()
	{
        $length = 0;
        $char = $this->_peek($length);
        while (preg_match(Zend_Yaml_Constants::ALPHA, $char)) {
            $length += 1;
            $char = $this->_peek($length);
        }
        if (empty($length)) {
            require_once 'Zend/Yaml/Exception.php';
            throw new Zend_Yaml_Exception('Expected alphanumeric character, but got ['.$char.']');
        }
        $value = $this->_prefix($length);
        $this->_forward($length);
        if (!preg_match(Zend_Yaml_Constants::NULL_SPACE_LINEBR, $this->_peek())) {
            require_once 'Zend/Yaml/Exception.php';
            throw new Zend_Yaml_Exception('Expected null byte [\0], space or linebreak character, but got ['.$char.']');
        }
        return $value;
    }

    private function _scanYamlDirectiveValue()
	{
        while ($this->_peek() == chr(32)) {
            $this->_forward();
        }
        $bigun = $this->_scanYamlDirectiveNumber();
        if ($this->_peek() !== '.') {
            require_once 'Zend/Yaml/Exception.php';
            throw new Zend_Yaml_Exception('Expected a digit or space, but got ['.$this->_peek().']');
        }
        $this->_forward();
        $littleun = $this->_scanYamlDirectiveNumber();
        if (!preg_match(Zend_Yaml_Constants::NULL_SPACE_LINEBR, $this->_peek())) {
            require_once 'Zend/Yaml/Exception.php';
            throw new Zend_Yaml_Exception('Expected null byte [\0], space or linebreak character, but got ['.$char.']');
        }
        return array($bigun, $littleun);
    }

    private function _scanYamlDirectiveNumber()
	{
        $char = $this->_peek();
        if (!ctype_digit($char)) {
            require_once 'Zend/Yaml/Exception.php';
            throw new Zend_Yaml_Exception('Expected digit, but got ['.$char.']');
        }
        $length = 0;
        while (ctype_digit($this->_peek($length))) {
            $length += 1;
        }
        $value = $this->_prefix($length);
        $this->_forward($length);
        return $value;
    }

    private function _scanTagDirectiveValue() ()
	{
       while ($this->_peek() == chr(32)) {
           $this->_forward();
       } 
       $handle = $this->_scanTagDirectiveHandle();
       while ($this->_peek() == chr(32)) {
           $this->_forward();
       }
       $prefix = $this->_scanTagDirectivePrefix();
       return array($handle, $prefix);
    }

    private function _scanTagDirectiveHandle()
	{
        $value = $this->_scanTagHandle('directive');
        if ($this->_peek() !== chr(32)) {
            require_once 'Zend/Yaml/Exception.php';
            throw new Zend_Yaml_Exception('Expected space, but got ['.$this->_peek().']');
        }
        return $value;
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

    /**
     * Proxy methods to Zend_Yaml_Buffer   ************************************
     */

    private function _peek($index = 0)
	{
        return $this->_buffer->peek($index);
    }

    private function _prefix($length = 1)
	{
        return $this->_buffer->prefix($index);
    }

    private function _forward($length = 1)
	{
        return $this->_buffer->forward($index);
    }

    private function _checkPrintable($string)
	{
        return $this->_buffer->checkPrintable($string);
    }

    /**
     * Other internal utils   *************************************************
     */

    private function arrayInsert($array, $position, array $elements)
    {
        if ($position < 0 || $position > count($array)) {
            require_once('Zend/Yaml/Exception.php');
            throw new Zend_Yaml_Exception('Array position is out of bounds');
        } else {
            $left = array_slice($array, 0, $position);
            $right = array_slice($array, $position);
            $array = array_merge($left, $elements, $right);
            unset($left, $right, $insert);
        }
        return $array;
    }

}