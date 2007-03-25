<?php

class Zend_Yaml_Constants
{
    /** Character constants */
    const LINEBR = "/[\n\x85]|(?:\r[^\n])/";
    const NONPRINTABLE = "/[^\x09\x0A\x0D\x20-\x7E\x85\xA0-\xFF]/";
    const ENDING_START = "/^(---|\.\.\.)[\0 \t\r\n\x85]$/";
    const ENDING = "/^---[\0 \t\r\n\x85]$/";
    const START = "/^\.\.\.[\0 \t\r\n\x85]$/";
    const BEG = "/^([^\0 \t\r\n\x85\-?:,\[\]{}#&*!|>'\"%@]|([\-?:][^\0 \t\r\n\x85]))/";
    const NULL_LINEBR = "/[\0\r\n\x85]/";
    const ALPHA = "/[-0-9A-Za-z_]/";
    const NULL_SPACE_LINEBR_ = "/[\0 \r\n\x85]/";
    const NULL_SPACE_TAB_LINEBR = "/[\0 \t\r\n\x85]/";
    const NON_ALPHA = "/[^-0-9A-Za-z_]/";
    const NON_ALPHA_OR_NUM = "/[\0 \t\r\n\x85?:,]}%@`]/";
    const SPACE_TAB = "/[ \t]/";
    const OPERATOR = "/[+-]/";
    const SPACE_LINEBR = "/[ \r\n\x85]/";
    const WIN32_LINEBR = "/[\r\n\x85]/";
    const NON_HEX = "/[^0-9A-Fa-f]/";
    const HEX = "/[0-9A-Fa-f]/";
    const STRANGE = "/[\]\[\-';\/?:@&=+$,.!~*()%\w]/";
    const R_FLOWZERO = "/[\0 \t\r\n\x85]|(:[\0 \t\r\n\x28])/";
    const R_FLOWNONZERO = "/[\0 \t\r\n\x85\[\]{},:?]/";
    const DOUBLE_ESC = "/[\"\\]/";
    const S4 = "/[\0 \t\r\n\x28[]{}]/";
}