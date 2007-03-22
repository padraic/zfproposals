<?php

class Zend_Yaml_Constants
{
    /** Character constants */
    const LINEBR = "\n\u0085\u2028\u2029";
    const NULL_BL_LINEBR = "\0 \r\n\u0085";
    const NULL_BL_T_LINEBR = "\0 \t\r\n\u0085";
    const NULL_OR_OTHER = self::NULL_BL_T_LINEBR;
    const NULL_OR_LINEBR = "\0\r\n\u0085";
    const FULL_LINEBR = "\r\n\u0085";
    const BLANK_OR_LINEBR = " \r\n\u0085";
    const S4 = "\0 \t\r\n\u0028[]{}";    
    const ALPHA = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ-_";
    const STRANGE_CHAR = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789][-';/?:@&=+$,.!~*()%";
    const RN = "\r\n";
    const BLANK_T = " \t";
    const SPACES_AND_STUFF = "'\"\\\0 \t\r\n\u0085";
    const DOUBLE_ESC = "\"\\";
    const NON_ALPHA_OR_NUM = "\0 \t\r\n\u0085?:,]}%@`";

    /** Regex pattern constants */
    const NON_PRINTABLE = "%[^\u0009\n\r\u0020-\u007E\u0085\u00A0-\u00FF]%";
    const NOT_HEXA = "%[^0-9A-Fa-f]%";
    const NON_ALPHA = "%[^-0-9A-Za-z_]%";
    const R_FLOWZERO = "%[\0 \t\r\n\u0085]|(:[\0 \t\r\n\u0028])%";
    const R_FLOWNONZERO = "%[\0 \t\r\n\u0085\\[\\]{},:?]%";
    const LINE_BR_REG = "%[\n\u0085]|(?:\r[^\n])%";
    const END_OR_START = "%^(---|\\.\\.\\.)[\0 \t\r\n\u0085]$%";
    const ENDING = "%^---[\0 \t\r\n\u0085]$%";
    const START = "%^\\.\\.\\.[\0 \t\r\n\u0085]$%";
    const BEG = "%^([^\0 \t\r\n\u0085\\-?:,\\[\\]{}#&*!|>'\"%@]|([\\-?:][^\0 \t\r\n\u0085]))%";
}