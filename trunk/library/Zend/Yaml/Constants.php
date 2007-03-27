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
    const FULL_LINEBR = "/[\r\n\x85]/";
    const NON_HEX = "/[^0-9A-Fa-f]/";
    const HEX = "/[0-9A-Fa-f]/";
    const STRANGE = "/[\]\[\-';\/?:@&=+$,.!~*()%\w]/";
    const FLOWZERO = "/[\0 \t\r\n\x85]|(:[\0 \t\r\n\x28])/";
    const FLOWNONZERO = "/[\0 \t\r\n\x85\[\]{},:?]/";
    const DOUBLE_ESC = "/[\"\\]/";
    const S4 = "/[\0 \t\r\n\x28[]{}]/";
    const SPACES_QUOTES_BACKSLASH_NULL_TAB_LINEBR = "/['\"\\\0 \t\r\n\x85]/";

    const UNESCAPES = "/[0abt\tnvfre \"\\N_]/"
    public static $UNESCAPES_ARRAY = array(
        "0"  =>   "\0",
        "a"  =>   "\x07",
        "b"  =>   "\x08",
        "t"  =>   "\x09",
        "\t" =>   "\x09",
        "n"  =>   "\x0A",
        "v"  =>   "\x0B",
        "f"  =>   "\x0C",
        "r"  =>   "\x0D",
        "e"  =>   "\x1B",
        " "  =>   "\x20",
        '"'  =>   "\"",
        "\\" =>   "\\",
        "N"  =>   "\x85",
        "_"  =>   "\xA0"
    );

    const ESCAPE_CODES = "/[x]/";
}