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
    const EOF = -1;

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
        // DEBUG INFINITE LOOP ON '#'
        $_otherPrintableChars = array(
            chr(9), chr(10), chr(13), chr(0x85)
        );
        $char = ord($character);
        if ($char >= 0x20 && $char <= 0x7e) {
            return true;
        }
        if (in_array($character, $_otherPrintableChars)) {
            return true;
        }
        return false;
    }

    public function isWord($character)
    {
        $char = ord($character);
        if (($char >= 0x41 && $char <= 0x5a)
            || ($char >= 0x61 && $char <= 0x7a)
            || ($char >= 0x30 && $char <= 0x39)
            || $character == '-') {
            return true;
        }
        return false;
    }

    public function isLine($character)
    {
        $_lineCharExceptions = array(
            chr(9), chr(10), chr(13), chr(0x20), chr(0x85)
        );
        if (in_array($character, $_lineCharExceptions)) {
            return false;
        }
        return $this->isPrintable($character);
    }

    public function isLineSpace($character)
    {
        $_lineSpaceChars = array(
            chr(10), chr(13), chr(0x85)
        );
        if (in_array($character, $_lineSpaceChars)) {
            return false;
        }
        return $this->isPrintable($character);
    }

    public function isSpace($character)
    {
        $_spaceChars = array(
            chr(9), chr(0x20)
        );
        if (in_array($character, $_spaceChars)) {
            return true;
        }
        return false;
    }

    public function isLineBreak($character)
    {
        $_lineBreakChars = array(
            chr(10), chr(13)
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
        return $character == chr(0x20);
    }

    public function isIndicator($character)
    {
        $indicators = "-:[]{},?*&!|#@%^'\"";
        if (strpos($indicators, $character) !== false) {
            return true;
        }
        return false;
    }

    public function isIndicatorSpace($character)
    {
        $indicators = "-:";
        if (strpos($indicators, $character) !== false) {
            return true;
        }
        return false;
    }

    public function isIndicatorInline($character)
    {
        $indicators = ",{}[]";
        if (strpos($indicators, $character) !== false) {
            return true;
        }
        return false;
    }

    public function isIndicatorNonSpace($character)
    {
        $indicators = "?*&!]|#@%^\"'";
        if (strpos($indicators, $character) !== false) {
            return true;
        }
        return false;
    }

    public function isIndicatorSimple($character)
    {
        $indicators = ",[]{}:";
        if (strpos($indicators, $character) !== false) {
            return true;
        }
        return false;
    }

    public function isIndicatorLooseSimple($character)
    {
        $indicators = ",{}[]";
        if (strpos($indicators, $character) !== false) {
            return true;
        }
        return false;
    }

}