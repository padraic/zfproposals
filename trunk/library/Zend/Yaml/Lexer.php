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
        $return = new Zend_Yaml_Token_Directive;
        $return->setName($name);
        $return->setValue($value);
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
            throw new Zend_Yaml_Exception('Expected null byte [\0], space or linebreak character, but got ['.$this->_peek().']');
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
        $value = $this->_scanTagUri('directive');
        if (!preg_match(Zend_Yaml_Constants::NULL_SPACE_LINEBR, $this->_peek())) {
            require_once 'Zend/Yaml/Exception.php';
            throw new Zend_Yaml_Exception('Expected null byte [\0], space or linebreak character, but got ['.$this->_peek().']');
        }
        return $value;
    }

    private function _scanDirectiveIgnoredLine()
	{
        while ($this->_peek() == chr(32)) {
            $this->_forward();
        }
        if ($this->_peek() == '#') {
            while (!preg_match(Zend_Yaml_Constants::NULL_SPACE_LINEBR), $this->_peek()) {
                $this->_forward();
            }
        }
        if (!preg_match(Zend_Yaml_Constants::NULL_LINEBR, $this->_peek())) {
            require_once 'Zend/Yaml/Exception.php';
            throw new Zend_Yaml_Exception('Expected a comment, null byte [\0], or linebreak character, but got ['.$this->_peek().']');
        }
        $this->_scanLineBreak();
    }

    private function _scanAnchor(Zend_Yaml_Token $token)
	{
        $this->_forward();
        $length = 0;
        $chunkSize = 16;
        while (true) {
            $chunk = $this->_prefix($chunkSize);
            $matches = null;
            preg_match(Zend_Yaml_Constants::NON_ALPHA, $chunk, $matches);
            $length = strpos($chunk, $matches[0]);
            if ($length) {
                break;
            }
            $chunkSize += 16;
        }
        if ($length == 0) {
            require_once 'Zend/Yaml/Exception.php';
            throw new Zend_Yaml_Exception('Expected alphanumeric, hyphen or underscore characters but found none');
        }
        $value = $this->_prefix($length);
        $this->_forward($length);
        if (!preg_match(Zend_Yaml_Constants::NON_ALPHA_OR_NUM, $this->_peek())) {
            require_once 'Zend/Yaml/Exception.php';
            throw new Zend_Yaml_Exception('Expected alphanumeric character, but got ['.$this->_peek().']');
        }
        $token->setValue($value);
        return $token;
    }

    private function _scanTag()
	{
        $char = $this->_peek(1);
        if ($char == '?') {
            $handle = null;
            $this->_forward(2);
            $suffix = $this->_scanTagUri('tag');
            if ($this->_peek() !== '>') {
                require_once 'Zend/Yaml/Exception.php';
                throw new Zend_Yaml_Exception('Expected [>] as closing tag, but got ['.$this->_peek().']');
            }
            $this->_forward();
        } elseif (preg_match(Zend_Yaml_Constants::NULL_SPACE_TAB_LINEBR, $char)) {
            $handle = null;
            $suffix = '!';
            $this->_forward();
        } else {
            $length = 1;
            $useHandle = false;
            while (!preg_match(Zend_Yaml_Constants::NULL_SPACE_TAB_LINEBR, $char)) {
                if ($char = '!') {
                    $useHandle = true;
                    break;
                }
                $length += 1;
                $char = $this->_peek($length);
            }
            $handle = '!';
            if ($useHandle) {
                $handle = $this->_scanTagHandle('tag');
            } else {
                $this->_forward();
            }
            $suffix = $this->_scanTagUri('tag');
        }
        if (!preg_match(Zend_Yaml_Constants::NULL_SPACE_LINEBR, $this->_peek())) {
            require_once 'Zend/Yaml/Exception.php';
            throw new Zend_Yaml_Exception('Expected null byte [\0], space or linebreak character, but got ['.$this->_peek().']');
        }
        $value = array($handle, $suffix);
        $return = new Zend_Yaml_Token_Tag;
        $return->setValue($value);
        return $return;
    }

    private function _scanBlockScalar($style)
	{
        $folded = ($style == '>');
        $chunks = array();
        $this->_forward();
        list($chomping, $increment) = $this->_scanBlockScalarIndicators();
        $this->_scanBlockScalarIgnoredLine();
        $minIndent = $this->_indent + 1;
        if ($minIndent < 1) {
            $minIndent = 1;
        }
        if (empty($increment)) {
            list($breaks, $maxIndent) = $this->_scanBlockScalarIndentation();
            $indent = max($minIndent, $maxIndent);
        } else {
            $indent = $minIndent + $increment - 1;
            $breaks = $this->_scanBlockScalarBreaks($indent);
        }
        $lineBreak = '';
        while ($this->_column == $indent && $this->_peek() !== "\0") {
            $chunks = array_merge($chunks, $breaks);
            $leadingNonSpace = !preg_match(Zend_Yaml_Constants::SPACE_TAB, $this->_peek());
            $length = 0;
            while (!preg_match(Zend_Yaml_Constants::NULL_LINEBR, $this->_peek($length))) {
                $length += 1;
            }
            $chunks[] = $this->_prefix($length);
            $this->_forward($length);
            $lineBreak = $this->_scanLineBreak();
            $breaks = $this->_scanBlockScalarBreaks($indent);
            if ($this->_column == $indent && $this->_peek() !== "\0") {
                if ($folded && $lineBreak == "\n" && $leadingNonSpace && !preg_match(Zend_Yaml_Constants::SPACE_TAB, $this->_peek())) {
                    if(empty($breaks)) $chunks[] = chr(32);
                } else {
                    $chunks[] = $lineBreak;
                }
            } else {
                break;
            }
        }
        if (!empty($chomping)) {
            $chunks[] = $lineBreak;
            $chunks = array_merge($chunks, $breaks);
        }
        $return = new Zend_Yaml_Token_Scalar;
        $return->setValue(implode('', $chunks));
        $return->setPlain(false);
        $return->setStyle($style);
        return $return;
    }

    private function _scanBlockScalarIndicators()
	{
        $chomping = null;
        $increment = null;
        $char = $this->_peek();
        if (preg_match(Zend_Yaml_Constants::OPERATOR, $char)) {
            $chomping = ($char == '+');
            $this->_forward();
            $char = $this->_peek();
            if (ctype_digit($char)) {
                $increment = intval($char);
                if ($increment == 0) {
                    require_once 'Zend/Yaml/Exception.php';
                    throw new Zend_Yaml_Exception('Expected indentation indicator of [1-9], but got [0]');
                }
                $this->_forward();
            }
        } elseif (ctype_digit($char)) {
            $increment = intval($char);
            if ($increment == 0) {
                require_once 'Zend/Yaml/Exception.php';
                throw new Zend_Yaml_Exception('Expected indentation indicator of [1-9], but got [0]');
            }
            $this->_forward();
            $char = $this->_peek();
            if (preg_match(Zend_Yaml_Constants::OPERATOR, $char)) {
                $chomping = ($char == '+');
                $this->_forward();
            }
        }
        if (!preg_match(Zend_Yaml_Constants::NULL_SPACE_LINEBR, $this->_peek())) {
            require_once 'Zend/Yaml/Exception.php';
            throw new Zend_Yaml_Exception('Expected indentation indicators or chomping, but got ['.$this->_peek().']');
        }
        return array($chomping, $increment);
    }

    private function _scanBlockScalarIgnoredLine()
	{
        while ($this->_peek() == chr(32)) {
            $this->_forward();
        }
        if ($this->_peek() == '#') {
            while (!preg_match(Zend_Yaml_Constants::NULL_OR_LINEBR, $this->_peek())) {
                $this->_forward();
            }
        }
        if (!preg_match(Zend_Yaml_Constants::NULL_OR_LINEBR, $this->_peek())) {
            require_once 'Zend/Yaml/Exception.php';
            throw new Zend_Yaml_Exception('Expected comment or linebreak, but got ['.$this->_peek().']');
        }
        $this->_scanLineBreak();
    }

    private function _scanBlockScalarIndentation()
	{
        $chunks = array();
        $maxIndent = 0;
        while (preg_match(Zend_Yaml_Constants::SPACE_LINEBR, $this->_peek())) {
            if ($this->_peek() !== chr(32)) {
                $chunks[] = $this->_scanLineBreak();
            } else {
                $this->forward(1);
                if ($this->_column > $maxIndent) {
                    $maxIndent = $this->_column;
                }
            }
        }
        return array($chunks, $maxIndent);
    }

    private function _scanBlockScalarBreaks($indent)
	{
        $chunks = [];
        while ($this->_column < $indent && $this->_peek() == chr(32)) {
            $this->_forward();
        }
        while (preg_match(Zend_Yaml_Constants::FULL_LINEBR, $this->_peek())) {
            $chunks[] = $this->_scanLineBreak();
        }
        while ($this->_column < $indent && $this->_peek() == chr(32)) {
            $this->_forward();
        }
        return $chunks;
    }

    private function _scanFlowScalar($style)
	{
        $double = ($style == '"');
        $chunks = array();
        $quote = $this->_peek();
        $this->_forward();
        $chunks = array_merge($chnks, $this->_scanFlowScalarNonSpaces($double));
        while ($this->_peek() !== $quote) {
            $chunks = array_merge($chunks, $this->_scanFlowScalarSpaces($double));
            $chunks = array_merge($chunks, $this->_scanFlowScalarNonSpaces($double));
        }
        $this->_forward();
        $return = new Zend_Yaml_Token_Scalar;
        $return->setValue(implode('', $chunks));
        $return->setPlain(false);
        $return->setStyle($style);
        return $return;
    }

    private function _scanFlowScalarNonSpaces($double)
	{
        $chunks = array();
        while (true) {
            $length = 0;
            while (!preg_match(Zend_Yaml_Constants::SPACES_QUOTES_BACKSLASH_NULL_TAB_LINEBR, $this->_peek($length))) {
                $length += 1;
            }
            if ($length !== 0) {
                $chunks[] = $this->_prefix($length);
                $this->_forward($length);
            }
            $char = $this->_peek();
            if (!$double && $char = '\'' && $this->_peek(1) == '\'') {
                $chunks[] = '\'';
                $this->_forward(2);
            } elseif (($double && $char == '\'') || (!$double && preg_match(Zend_Yaml_Constants::DOUBLE_ESC, $char))) {
                $chunks[] = $char;
                $this->_forward();
            } elseif ($double && $char == '\\') {
                $this->_forward();
                $char = $this->_peek();
                if (preg_match(Zend_Yaml_Constants::UNESCAPES, $char)) {
                    $chunks[] = Zend_Yaml_Constants::$UNESCAPES_ARRAY[$char];
                    $this->_forward();
                } elseif (preg_match(Zend_Yaml_Constants::ESCAPE_CODES, $char)) {
                    $length = Zend_Yaml_Constants::$ESCAPE_CODES_ARRAY[$char];
                    $this->_forward();
                    if (preg_match(Zend_Yaml_Constants::NON_HEX), $this->_prefix($length)) {
                        require_once 'Zend/Yaml/Exception.php';
                        throw new Zend_Yaml_Exception('Expected escape sequence of '.$length.' hex numbers, but got ['.$this->_prefix($length).']');
                    }
                    $hexdecimal = $this->_prefix($length);
                    $chunks[] = (string) hexdec($hexdecimal);
                    $this->_forward($length);
                } elseif (preg_match(Zend_Yaml_Constants::FULL_LINEBR, $char)) {
                    $this->_scanLineBreak();
                    $chunks = array_merge($chunks, $this->_scanFlowScalarBreaks($double));
                } else {
                    require_once 'Zend/Yaml/Exception.php';
                    throw new Zend_Yaml_Exception('Found unknown escape character ['.$char.']');
                }
            } else {
                return $chunks; // breaks while loop
            }
        }
    }

    private function _scanFlowScalarSpaces($double)
	{
        $chunks = array();  
        $length = 0;
        while (preg_match(Zend_Yaml_Constants::BLANK_TAB, $this->_peek($length))) {
            $length += 1;
        }
        $whiteSpaces = $this->_prefix($length);
        $this->_forward($length);
        $char = $this->_peek();
        if ($char == "\0") {
            require_once 'Zend/Yaml/Exception.php';
            throw new Zend_Yaml_Exception('Unexpected end of buffer stream');
        } elseif (preg_match(Zend_Yaml_Constants::FULL_LINEBR, $char)) {
            $lineBreak = $this->_scanLineBreak();
            $breaks = $this->_scanFlowScalarBreaks($double);
            if ($lineBreak != "\n") {
                $chunks[] = $lineBreak;
            } elseif (empty($breaks)) {
                $chunks[] = chr(32);
            }
            $chunks = array_merge($chunks, $breaks);
        } else {
            $chunks[] = $whiteSpaces;
        }
    }

    private function _scanFlowScalarBreaks()
	{
        $chunks = array();
        while (true) {
            $prefix = $this->_prefix(3);
            if ($prefix == '---' || $prefix = '...' && preg_match(Zend_Yaml_Constants::NULL_SPACE_TAB_LINEBR, $this->_peek(3))) {
                require_once 'Zend/Yaml/Exception.php';
                throw new Zend_Yaml_Exception('Unexpected document separator');
            }
            while (preg_match(Zend_Yaml_Constants::SPACE_TAB, $this->_peek())) {
                $this->_forward();
            }
            if (preg_match(Zend_Yaml_Constants::FULL_LINEBR, $this->_peek())) {
                $chunks[] = $this->_scanLineBreak();
            } else {
                return $chunks; // ends while loop
            }
        }
    }

    private function _scanPlain()
	{
        $chunks = array();
        $indent = $this->_indent + 1;
        $spaces = array();
        if (empty($this->_flowLevel)) {
            $flowNonZero = false;
            $regex = Zend_Yaml_Constants::FLOWZERO;
        } else {
            $flowNonZero = true;
            $regex = Zend_Yaml_Constants::FLOWNONZERO;
        }

        while ($this->_peek() != '#') {
            $length = 0;
            $chunkSize = 32;
            while ($length == 0) {
                $prefix = $this->_prefix($chunkSize);
                $matches = null;
                preg_match($regex, $prefix, $matches);
                $lengthTemp = strpos($prefix, $matches[0]);
                if (!empty($lengthTemp)) {
                    $length = $lengthTemp
                }
            }
            $char = $this->_peek($length);
            if ($flowNonZero && $char = ':' && !preg_match(Zend_Yaml_Constants::S4, $this->_peek($length + 1))) {
                $this->_forward($length);
                require_once 'Zend/Yaml/Exception.php';
                throw new Zend_Yaml_Exception('Unexpected colon [:] while scanning a plain scalar');
            }
            if ($length == 0) {
                break;
            }
            $this->_allowSimpleKey = false;
            $chunks = array_merge($chunks, $spaces);
            $chunks[] = $this->_prefix($length);
            $this->_forward($length);
            $spaces = $this->_scanPlainSpaces($indent);
            if (empty($spaces) || (empty($this->_flowLevel) && $this->_column < $indent)) {
                break;
            }
        }
        $return = new Zend_Yaml_Token_Scalar;
        $return->setValue(implode('', $chunks));
        $return->setPlain(true);
        return $return;
    }

    private function _scanPlainSpaces()
	{
        $chunks[] = array();
        $length = 0;
        while ($this->_peek($length) = chr(32)) {
            $length += 1;
        }
        $whiteSpaces = $this->_prefix($length);
        $this->_forward($length);
        $char = $this->_peek();
        if (preg_match(Zend_Yaml_Constants::FULL_LINEBR, $char)) {
            $lineBreak = $this->_scanLineBreak();
            $this->_allowSimpleKey = true;
            if (preg_match(Zend_Yaml_Constants::ENDING_START, $this->_prefix(4))) {
                return;
            }
            $breaks = array();
            while (preg_match(Zend_Yaml_Constants::SPACE_LINEBR, $this->_peek())) {
                if ($this->_peek() == chr(32)) {
                    $this->_forward();
                } else {
                    $breaks[] = $this->_scanLineBreak();
                    if (preg_match(Zend_Yaml_Constants::ENDING_START, $this->_prefix(4))) {
                        return;
                    }
                }
            }
            if ($lineBreak !== "\n") {
                $chunks[] = $lineBreak;
            } elseif (empty($breaks)) {
                $chunks[] = chr(32);
            }
            $chunks = array_merge($chunks, $breaks);
        } else {
            $chunks[] = $whiteSpaces;
        }
        return $chunks;
    }

    private function _scanTagHandle($name)
	{
        $char = $this->_peek();
        if ($char !== '!') {
            require_once 'Zend/Yaml/Exception.php';
            throw new Zend_Yaml_Exception('Expecting [!], but got ['.$char.'] while scanning a '.$name);  
        }
        $length = 1;
        $char = $this->_peek($length);
        if ($char == chr(32)) {
            while (preg_match(Zend_Yaml_Constants::ALPHA, $char)) {
                $length += 1;
                $char = $this->_peek($length);
            }
            if ($char !== '!') {
                $this->_forward($length);
                require_once 'Zend/Yaml/Exception.php';
                throw new Zend_Yaml_Exception('Expecting [!], but got ['.$char.'] while scanning '.$name);
            }
            $length += 1;
        }
        $return = $this->_prefix($length);
        $this->_forward($length);
        return $return;
    }

    private function _scanTagUri($name)
	{
        $chunks = array();
        $length = 0;
        $char = $this->_peek($length);
        while (preg_match(Zend_Yaml_Constants::STRANGE, $char)) {
            if ($char == '%') {
                $chunks[] = $this->_prefix($length);
                $this->_forward($length);
                $length = 0;
                $chunks[] = $this->_scanUriEscapes($name);
            } else {
                $length += 1;
            }
            $char = $this->_peek($length);
        }
        if ($length !== 0) {
            $chunks[] = $this->_prefix($length);
            $this->_forward($length);
        }
        if (empty($chunks) {
           require_once 'Zend/Yaml/Exception.php';
           throw new Zend_Yaml_Exception('Expecting URI, but got ['.$char.'] while scanning '.$name); 
        }
        return implode('', $chunks);
    }

    private function _scanUriEscapes()
	{
        $bytes = array();
        while ($this->_peek() == '%') {
            $this->_forward();
            if (!preg_match(Zend_Yaml_Constants::HEX, $this->_peek(1)) || !preg_match(Zend_Yaml_Constants::HEX, $this->_peek(2))) {
                require_once 'Zend/Yaml/Exception.php';
                throw new Zend_Yaml_Exception('Expecting URI escape sequence of 2 hexdecimal numbers, but got ['.$this->_peek(1).'] and ['.$this->_peek(2).'] while scanning '.$name);
            }
            $bytes[] = (string) hexdec($this->_prefix(2));
            $this->_forward(2);
        }
        return implode('', $bytes);
    }

    private function _scanLineBreak()
	{
        if (preg_match(Zend_Yaml_Constants::FULL_LINEBR, $this-._peek())) {
        
        }
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