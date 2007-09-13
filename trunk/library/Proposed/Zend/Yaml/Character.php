<?php

class Zend_Yaml_Character
{

    /** Constants representing valid character types */
    const PRINTABLE = 'printable';
    const WORD = 'word';
    const LINE = 'line';
    const LINESPACE = 'linespace';
    const SPACE = 'space';
    const LINEBREAK = 'linebreak';
    const DIGIT = 'digit';
    const INDENT = 'indent';
    const EOF = 'eof';
    
    public function isType($character, $type)
    {
        switch ($type) {
            case self::PRINTABLE:
                return $this->isPrintable($character);
                break;
            case self::WORD:
                return $this->isWord($character);
                break;
            case self::LINE:
                return $this->isLine($character);
                break;
            case self::LINESPACE:
                return $this->isLineSpace($character);
                break;
            case self::SPACE:
                return $this->isSpace($character);
                break;
            case self::LINEBREAK:
                return $this->isLineBreak($character);
                break;
            case self::DIGIT:
                return $this->isDigit($character);
                break;
            case self::INDENT:
                return $this->isIndent($character);
                break;
            default:
                return false;
        }
    }

    public function isPrintable($character)
    {
    
    }

    public function isWord($character)
    {
    
    }

    public function isLine($character)
    {
    
    }

    public function isLineSpace($character)
    {
    
    }

    public function isSpace($character)
    {
    
    }

    public function isLineBreak($character)
    {
        $_lineBreakChars = array(
            chr(10), chr(13), 0x85, 0x2028, 0x2029
        );
        if (in_array($character, $_lineBreakChars)) {
            return true;    
        }
        return false;
    }

    public function isDigit($character)
    {
        return preg_match("/^\d$/", $character);
    }

    public function isIndent($character)
    {
        return $character == '';
    }

}