<?php

require_once 'Zend/Yaml/Parser/Reader.php';
require_once 'Zend/Yaml/Character.php';
require_once 'Zend/Yaml/Parser/Event/Interface.php';

// event based byte parser
// should be easily modified to switch to Unicode (possibly UCS-4 as a fixed width char array).

class Zend_Yaml_Parser
{

    /** Event constants */
    const LIST_OPEN = '[';
    const LIST_CLOSE = ']';
    const MAP_OPEN = '{';
    const MAP_CLOSE = '}';
    const LIST_NO_OPEN = 'n';
    const MAP_NO_OPEN = 'N';
    const DOCUMENT_HEADER = 'H';
    const MAP_SEPARATOR = ':';
    const LIST_ENTRY = '-';

    const SINGLE_QUOTE_STYLE = 'single';
    const DOUBLE_QUOTE_STYLE = 'double';

    protected $_reader = null;
    protected $_line = 1;
    protected $_event = null;
    protected $_character = null;
    protected $_properties = array();
    protected $_pendingEvent = '';

    public function __construct($input, $event, Zend_Yaml_Character $yamlCharacter = null)
    {
        $this->_reader = new Zend_Yaml_Parser_Reader($input);
        $this->_event = $event;
        if (is_null($yamlCharacter)) {
            $this->_character = new Zend_Yaml_Character;
        } else {
            $this->_character = $yamlCharacter;
        }
    }

    public static function parse($data)
    {
        $event = new Zend_Yaml_Parser_Event_Test; // need a proper DI method for Events
        $parser = new self($data, $event);
        $parser->parseYaml();
    }

    public function parseYaml()
    {
        while ($this->catchComment(-1, false)) {
        }
        if (!$this->catchHeader()) {
            $this->catchDocumentFirst();
        } else {
            $this->catchValueNa(-1);
        }
        while ($this->catchDocumentNext()) {
        }
    }

    public function countIndent()
    {
        $this->mark();
        $i = 0;
        $char = null;
        while ($this->_character->isType( $char = $this->_reader->read(), Zend_Yaml_Character::INDENT )) {
            $i++;
        }
        if ($char == "\t") {
            throw new Exception('Invalid indentation; spaces, and not tabs, should be used to indent YAML content');
        }
        $this->reset();
        return $i;
    }

    public function catchType($type)
    {
        $this->mark();
        $i = 0;
        while ($this->_character->isType($this->_reader->read(), $type)) {
            $i++;
        }
        if ($i !== 0) {
            $this->_reader->unread();
            $this->unmark();
            return true;
        }
        $this->reset();
        return false;
    }

    public function catchSpace()
    {
        return $this->catchType(Zend_Yaml_Character::SPACE);
    }

    public function catchLine()
    {
        return $this->catchType(Zend_Yaml_Character::LINE);
    }

    public function catchLineSpace()
    {
        return $this->catchType(Zend_Yaml_Character::LINESPACE);
    }

    public function catchWord()
    {
        return $this->catchType(Zend_Yaml_Character::WORD);
    }

    public function catchDigit()
    {
        return $this->catchType(Zend_Yaml_Character::DIGIT);
    }

    public function catchIndent($indent)
    {
        $this->mark();
        while ($this->_character->isType($this->_reader->read(), Zend_Yaml_Character::INDENT) && $indent > 0) {
            $indent--;
        }
        if ($indent == 0) {
            $this->_reader->unread();
            $this->unmark();
            return true;
        }
        $this->reset();
        return false;
    }

    public function catchNewLine()
    {
        $this->_line++;
        $this->mark();
        $catch1 = $this->_reader->read();
        $catch2 = $this->_reader->read();
        if ($catch1 == -1 || ($catch1 == chr(13) && $catch2 == chr(10))) {
            $this->unmark();
            return true;
        }
        if ($this->_character->isType($catch, Zend_Yaml_Character::LINEBREAK)) {
            $this->_reader->unread();
            $this->unmark();
            return true;
        }
        $this->reset();
        $this->_line--;
        return false;
    }


    public function catchEnd()
    {
        $this->mark();
        $this->catchSpace();
        if (!$this->catchNewLine()) {
            $this->reset();
            return false;
        }
        while ($this->catchComment(-1, false)) {
            // we're not parsing comments; just skipping through
        }
        $this->unmark();
        return true;
    }

    public function catchStringSimple()
    {
        $char = '';
        $i = 0;
        $dashFirst = false;
        $this->mark();

        while (true) {
            $char = $this->_reader->read();
            if ($char == -1) { // eof
                break;
            }
            if ($i == 0 && $char == '-') {
                $dashFirst = true;
                continue;
            }
            if ($i == 0 && ($this->_character->isSpace($char) || $this->_character->isIndicatorNonSpace($char) || $this->_character->isIndicatorSpace($char))) {
                break;
            }
            if ($dashFirst === true && ($this->_character->isSpace($char) || $this->_character->isLineBreak($char))) {
                $this->unmark();
                return false;
            }
            if (!$this->_character->isLineSpace($char) || ($this->_character->isIndicatorSimple($char) && $this->_reader->previous() !== '\\')) {
                break;
            }
            $i++;
        }

        $this->_reader->unread();
        $this->unmark();
        if ($i !== 0) {
            return true;
        }
        return false;
    }

    public function catchStringLooseSimple()
    {
        $char = '';
        $i = 0;

        while (true) {
            $char = $this->_reader->read();
            if ($char == -1) {
                break;
            }
            if (!$this->_character->isLineSpace($char) || ($this->_character->isIndicatorLooseSimple($char) && $this->_reader->previous() !== '\\')) {
                break;
            }
            $i++;
        }

        $this->_reader->unread();
        if ($i == 0) {
            if ($this->_character->isLineBreak($char)) {
                return true;
            }
            return false;
        }
        return true;
    }

    public function catchStringQuoted($quoteStyle)
    {
        $char = '';
        $i = 0;

        if ($quoteStyle == self::SINGLE_QUOTE_STYLE) {
            $q = "'";
        } else {
            $q = '"';
        }

        if ($this->_reader->current() !== $q) {
            return false;
        }
        $this->_reader->read(); // read past the single quote

        while ($this->_character->isType($char = $this->_reader->read(), Zend_Yaml_Character::LINESPACE)) {
            if ($char == $q && $this->reader->previous() !== '\\') {
                break;
            }
            $i++;
        }
        if ($char !== $q) {
            throw new Exception('Reached of quoted String but did not find expected quote terminator');
        }
        return true;
    }

    public function catchStringSingleQuoted()
    {
        return $this->catchStringQuoted(self::SINGLE_QUOTE_STYLE);
    }

    public function catchStringDoubleQuoted()
    {
        return $this->catchStringQuoted(self::DOUBLE_QUOTE_STYLE);
    }

    public function catchStringLoose()
    {
        $this->mark();
        $q1 = $this->catchStringSingleQuoted();
        $q2 = $this->catchStringDoubleQuoted();


        if ($q1 || $q2 || $this->catchStringLooseSimple()) {
            // some value worth storing
            $string = trim( (string) $this->_reader );
            if ($q2) {
                $string = $this->fixDoubleQuotedString($string);
            } elseif ($q1) {
                $string = $this->fixSingleQuotedString($string);
            }
            if ($q1||$q2) {
                $this->_properties['string'] = $string;
            } else { // not a quoted string, just a quoteless loose str
                if (empty($string)) {
                    $this->_properties['value'] = null;
                } else {
                    $this->_properties['value'] = $string;
                }
            }

            $this->unmark();
            return true;
        }

        $this->reset();
        return false;
    }

    public function catchString()
    {
        $this->mark();
        $q1 = $this->catchStringSingleQuoted();
        $q2 = $this->catchStringDoubleQuoted();

        if ($q1 || $q2 || $this->catchStringSimple()) {
            $string = trim( (string) $this->_reader );
            if ($q2) {
                $string = $this->fixDoubleQuotedString($string);
            } elseif ($q1) {
                $string = $this->fixSingleQuotedString($string);
            }
            if ($q1 || $q2) {
                $this->_properties['string'] = $string;
            } else { // quoteless simple string
                $this->_properties['value'] = $string;
            }
            $this->unmark();
            return true;
        }

        $this->reset();
        return false;
    }

    // fix quotes at some point

    public function catchAlias()
    {
        $this->mark();
        if ($this->_reader->read() !== '*') {
            $this->_reader->unread();
            $this->unmark();
            return false;
        }
        if (!$this->catchWord()) {
            $this->reset();
            return false;
        }
        $this->unmark();
        $this->_properties['alias'] = (string) $this->_reader;
        return true;
    }

    public function catchAnchor()
    {
        $this->mark();
        if ($this->_reader->read() != '&') {
            $this->_reader->unread();
            $this->unmark();
            return false;
        }
        if (!$this->catchWord()) {
            $this->reset();
            return false;
        }
        $this->unmark();
        $this->_properties['anchor'] = (string) $this->_reader;
        return true;
    }

    public function catchComment($n, $explicit)
    {
        $char = '';

        $this->mark();
        if ($n !== -1 && $this->countIndent() >= $n) {
            $this->reset();
            return false;
        }
        $this->catchSpace();
        $char = $this->_reader->read();
        if ($char == '#') {
            $this->catchLineSpace();
        } else {
            if ($char == -1) {
                $this->unmark();
                return false;
            }
            if ($explicit === true) {
                $this->reset();
                return false;
            } else {
                $this->_reader->unread();
            }
        }

        if ($this->catchNewLine() === false) {
            $this->reset();
            return false;
        }
        var_dump('comment stripped'); exit;
        $this->unmark();
        return true;
    }

    public function catchHeader()
    {
        $this->mark();
        $char_1 = $this->_reader->read();
        $char_2 = $this->_reader->read();
        $char_3 = $this->_reader->read();
        if ($char_1 != '-' || $char_2 != '-' || $char_3 != '-') {
            $this->reset();
            return false;
        }
        while ($this->catchSpace() && $this->catchDirective()) {
            // just skipping through
        }
        $this->unmark();
        $this->_event->setEvent(self::DOCUMENT_HEADER);
        return true;
    }

    public function catchDirective()
    {
        $this->mark();
        if ($this->_reader->read() !== '#') {
            $this->_reader->unread();
            $this->unmark();
            return false;
        }
        if (!$this->catchWord()) {
            $this->reset();
            return false;
        }
        if ($this->_reader->read() !== ':') {
            $this->reset();
            return false;
        }
        if (!$this->catchLine()) {
            $this->reset();
            return false;
        }
        $this->_event->setContent('directive', (string) $this->_reader);
        $this->unmark();
        return true;
    }

    public function catchTransfer()
    {
        $this->mark();
        if ($this->_reader->read() !== '!') {
            $this->_reader->unread();
            $this->unmark();
            return false;
        }
        if (!$this->catchLine()) {
            $this->reset();
            return false;
        }
        $this->_properties['transfer'] = (string) $this->_reader;
        $this->unmark();
        return true;
    }

    public function catchProperties()
    {
        // One catch to bind them all, and in the darkness bind them!
        $this->mark();
        if ($this->catchTransfer()) {
            $this->catchSpace();
            $this->catchAnchor();
            $this->unmark();
            return true;
        }
        if ($this->catchAnchor()) {
            $this->catchSpace();
            $this->catchTransfer();
            $this->unmark();
            return true;
        }
        $this->reset();
        return false;
    }

    public function catchKey($n)
    {
        if ($this->_reader->current() == '?') {
            $this->_reader->read();
            if (!$this->catchValueNested($n + 1)) {
                throw new Exception('Key indicator (\'?\') detected without a nested value');
            }
            if (!$this->catchIndent($n)) {
                throw new Exception('Indentations after a nested key incorrect');
            }
            return true;
        }
        if (!$this->catchValueInline()) {
            return false;
        }
        $this->catchSpace();
        return true;
    }

    public function catchValue($n)
    {
        if ($this->catchValueNested($n) || $this->catchValueBlock($n)) {
            return true;
        }
        if (!$this->catchValueLooseInline()) {
            return false;
        }
        if (!$this->catchEnd()) {
            throw new Exception('Inline value is unterminated');
        }
        return true;
    }

    public function catchValueNa($n)
    {
        if ($this->catchValueNested($n) || $this->catchValueBlock($n)) {
            return true;
        }
        if (!$this->catchValueInlineNa()) {
            return false;
        }
        if (!$this->catchEnd()) {
            throw new Exception('Inline value is unterminated');
        }
        return true;
    }

    public function catchValueInline()
    {
        $this->mark();
        if ($this->catchProperties()) {
            $this->catchSpace();
        }
        if ($this->catchAlias() || $this->catchString()) {
            $this->sendEvents();
            $this->unmark();
            return true;
        }
        if ($this->catchList() || $this->catchMap()) {
            $this->unmark();
            return true;
        }
        $this->removeEvents();
        $this->reset();
        return false;
    }

    public function catchValueLooseInline()
    {
        $this->mark();
        if ($this->catchProperties()) {
            $this->catchSpace();
        }
        if ($this->catchAlias() || $this->catchStringLoose()) {
            $this->sendEvents();
            $this->unmark();
            return true;
        }
        if ($this->catchList() || $this->catchMap()) {
            $this->unmark();
            return true;
        }
        $this->removeEvents();
        $this->reset();
        return false;
    }

    public function catchValueInlineNa()
    {
        $this->mark();
        if ($this->catchProperties()) {
            $this->catchSpace();
        }
        if ($this->catchString()) {
            $this->sendEvents();
            $this->unmark();
            return true;
        }
        if ($this->catchList() || $this->catchMap()) {
            $this->unmark();
            return true;
        }
        $this->removeEvents();
        $this->reset();
        return false;
    }

    public function catchValueNested($n)
    {
        $this->mark();
        if ($this->catchProperties()) {
            $this->catchSpace();
        }
        if (!$this->catchEnd()) {
            $this->removeEvents();
            $this->reset();
            return false;
        }
        $this->sendEvents();
        while ($this->catchComment($n, false)) {
            // passing through
        }
        if ($this->catchNlist($n) || $this->catchNmap($n)) {
            $this->unmark();
            return true;
        }
        $this->reset();
        return false;
    }

    public function catchValueBlock($n)
    {
        $this->mark();
        if ($this->catchProperties()) {
            $this->catchSpace();
        }
        if (!$this->catchBlock($n)) {
            $this->removeEvents();
            $this->reset();
            return false;
        }
        $this->sendEvents();
        while ($this->catchComment($n, false)) {
            // passing through
        }
        $this->unmark();
        return true;
    }

    public function catchNmap($n)
    {
        $this->mark();
        $i = 0;
        $indent = $this->countIndent();
        if ($n == -1) {
            $n = $indent;
        } elseif ($indent > $n) {
            $n = $indent;
        }

        $this->_pendingEvent = '{';
        while (true) {
            if (!$this->catchIndent($n)) {
                break;
            }
            if (!$this->catchNmapEntry($n)) {
                break;
            }
            $i++;
        }

        if ($i > 0) {
            $this->_event->setEvent(self::MAP_CLOSE);
            $this->unmark();
            return true;
        }
        $this->_pendingEvent = '';
        $this->reset();
        return false;
    }

    public function catchNmapEntry($n)
    {
        if (!$this->catchKey($n)) {
            return false;
        }
        if ($this->_reader->read() !== ':') {
            return false;
        }
        $this->_reader->read();
        $this->_event->setEvent(MAP_SEPARATOR);
        $this->catchSpace();
        if (!$this->catchValueLoose($n + 1)) {
            throw new Exception('No value after : separator');
        }
        return true;
    }

    public function catchNlist($n)
    {
        $this->mark();
        $i = 0;
        $indent = $this->countIndent();
        if ($n == -1) {
            $n = $indent;
        } elseif ($indent > $n) {
            $n = $indent;
        }
        $this->_pendingEvent = '[';
        while (true) {
            if (!$this->catchIndent($n)) {
                break;
            }
            if (!$this->catchNlistEntry($n)) {
                break;
            }
            $i++;
        }
        if ($i > 0) {
            $this->_event->setEvent(self::LIST_CLOSE);
            $this->unmark();
            return true;
        }
        $this->_pendingEvent = '';
        $this->reset();
        return false;
    }

    public function catchStartList()
    {
        $this->mark();
        if ($this->_reader->read() == '-') {
            if ($this->_character->isLineBreak( $this->_reader->current() ) || $this->catchSpace()) {
                $this->unmark();
                return true;
            }
        }
        $this->reset();
        return false;
    }

    public function catchNlistEntry($n)
    {
        if (!$this->catchStartList()) {
            return false;
        }
        $this->catchSpace();
        if ($this->catchNmapInList($n + 1) || $this->catchValue($n + 1)) {
            return true;
        }
        throw new Exception('Invalid nested list');
    }

    public function catchNmapInList($n)
    {
        $this->mark();
        if (!$this->catchString()) {
            $this->reset();
            return false;
        }
        if ($this->_pendingEvent == '[') {
            $this->_event->setEvent(self::LIST_OPEN);
            $this->_pendingEvent = '';
        }
        $this->_event->setEvent(self::MAP_OPEN);
        $this->sendEvents();
        $this->_event->setEvent(self::MAP_SEPARATOR);
        if (!$this->catchSpace()) {
            $this->reset();
            return false;
        }
        if (!$this->catchValue($n + 1)) {
            throw new Exception('No value after : separator in in-list map');
        }

        $n = $n + 1;
        $i = 0;
        $indent = $this->countIndent();
        if ($n == -1) {
            $n = $indent;
        } elseif ($indent > $n) {
            $n = $indent;
        }
        while (true) {
            if (!$this->catchIndent($n)) {
                break;
            }
            if (!$this->catchNmapEntry($n)) {
                break;
            }
            $i++;
        }
        $this->_event->setEvent(self::MAP_CLOSE);
        $this->unmark();
        return true;
    }

    public function catchBlock($n)
    {
        $char = $this->_reader->current();
        if ($char !== '|' && $char !== ']' && $char !== '>') {
            return false;
        }
        $this->_reader->read();
        if ($this->_reader->current() == '\\') {
            $this->_reader->read();
        }
        $this->catchSpace();
        if ($this->catchDigit()) {
            $this->catchSpace();
        }

        if (!$this->catchNewLine()) {
            throw new Exception('No newline detected after a block definition');
        }

        $string = '';
        $blockIndent = $this->getBlockLine($n, -1, $string, $char);

        while ($this->getBlockLine($n, $blockIndent, $string, $char) !== -1) {
            // keep looping till the clock runs out
        }

        if (strlen($string) > 0 && $this->_character->isLineBreak($string[strlen($string) - 1])) {
            $string = substr(0, strlen($string) - 1);
        }
        $this->_event->setContent('string', $string);
        return true;
    }

    public function getBlockLine($n, $blockIndent, &$string, $char)
    {
        $indent = 0;
        if ($blockIndent == -1) {
            $indent = $this->countIndent();
            if ($indent < $n) {
                return -1;
            }
            $n = $indent;
            $this->catchIndent($n);
        } else {
            $indent = $blockIndent;
            if (!$this->catchIndent($indent)) {
                return -1;
            }
        }

        if ($this->_reader->read() == -1) {
            return -1;
        }

        $this->mark();
        $this->catchLineSpace();
        $string .= (string) $this->_reader;
        $this->unmark();

        if ($char == '|') {
            $string .= "\n";
        } else {
            $string .= " ";
        }
        $this->catchNewLine();
        return $indent;
    }

    public function catchList()
    {
        if ($this->_reader->current() !== '[') {
            return false;
        }
        $this->_reader->read();
        $this->sendEvents();
        $this->_event->setEvent(self::LIST_OPEN);

        while ($this->catchListEntry()) {
            $char = $this->_reader->current();
            if ($char == ']') {
                $this->_reader->read();
                $this->_event->setEvent(self::LIST_CLOSE);
                return true;
            }
            if ($char !== ',') {
                throw new Exception('Did not find expected (\',\') within an in-line list');
            }
            $this->_reader->read();
        }

        $char = $this->_reader->current();
        if ($char == ']') {
            $this->_reader->read();
            $this->_event->setEvent(self::LIST_CLOSE);
            return true;
        } else {
            throw new Exception('In-line list error found');
        }
    }

    public function catchListEntry()
    {
        $this->catchSpace();
        if (!$this->catchValueLooseInline()) {
            return false;
        }
        $this->catchSpace();
        return true;
    }

    public function catchMap()
    {
        if ($this->_reader->current() !== '{') {
            return false;
        }
        $this->_reader->read();
        $this->sendEvents();
        $this->_event->setEvent(self::MAP_OPEN);

        while ($this->catchMapEntry()) {
            $char = $this->_reader->current();
            if ($char == '}') {
                $this->_reader->read();
                $this->_event->setEvent(self::MAP_CLOSE);
                return true;
            }
            if ($char !== ',') {
                throw new Exception('Did not find expected (\',\') inline map separator');
            }
            $this->_reader->read();
        }

        $char = $this->_reader->current();
        if ($char == '}') {
            $this->_reader->read();
            $this->_event->setEvent(self::MAP_CLOSE);
            return true;
        }
        throw new Exception('Error with an inline map');
    }

    public function catchMapEntry()
    {
        $this->catchSpace();
        if (!$this->catchValueInline()) {
            return false;
        }
        $this->catchSpace();
        if ($this->_reader->current !== ':') {
            return false;
        }
        $this->_reader->read();
        $this->_event->setEvent(self::MAP_SEPARATOR);
        if (!$this->catchSpace()) {
            throw new Exception('No space detected after map separator');
        }
        if (!$this->catchValueLooseInline()) {
            throw new Exception('No value detected after map separator');
        }
        $this->catchSpace();
        return true;
    }

    public function catchDocumentFirst()
    {
        $bool = ($this->catchNlist(-1) || $this->catchNmap(-1));
        $this->mark();
        exit('end: ' . $this->_reader->read());
        if (!$this->catchHeader() && $this->_reader->read() !== Zend_Yaml_Character::EOF) {
            throw new Exception('End of document was expected');
        }
        if (!$bool) {
            throw new Exception('First document is not a nested map or nested list');
        }
        return true;
    }

    public function catchDocumentNext()
    {
        if (!$this->catchHeader()) {
            return false;
        }
        if (!$this->catchValueNa(-1)) {
            return false;
        }
        return true;
    }

    public function mark()
    {
        $this->_reader->mark();
    }

    public function reset()
    {
        $this->_reader->reset();
    }

    public function unmark()
    {
        $this->_reader->unmark();
    }

    public function getEvent() {
        return $this->_event;
    }

    public function setEvent(Zend_Yaml_Parser_Event_Interface $event) {
        $this->_event = $event;
    }

    public function getLineNumber(){
        return $this->_line;
    }

    protected function getReaderString()
    {
        return $this->_reader->toString();
    }

    protected function removeEvents()
    {
        $this->_properties = array();
    }

    protected function sendEvents()
    {
        $string = null;
        if ($this->_pendingEvent == '[') {
            $this->_event->setEvent(self::LIST_OPEN);
        }
        if ($this->_pendingEvent == '{') {
            $this->_event->setEvent(self::MAP_OPEN);
        }
        $this->_pendingEvent = '';

        if (isset($this->_properties['anchor'])) {
            $string = $this->_properties['anchor'];
        } else {
            $string = null;
        }
        if ($string !== null) {
            $this->_event->setProperty('anchor', $string);
        }

        if (isset($this->_properties['transfer'])) {
            $string = $this->_properties['transfer'];
        } else {
            $string = null;
        }
        if ($string !== null) {
            $this->_event->setProperty('transfer', $string);
        }

        if (isset($this->_properties['alias'])) {
            $string = $this->_properties['alias'];
        } else {
            $string = null;
        }
        if ($string !== null) {
            $this->_event->setContent('alias', $string);
        }

        if (isset($this->_properties['string'])) {
            $this->_event->setContent('string', $this->_properties['string']);
        }
        if (isset($this->_properties['value'])) {
            $this->_event->setContent('value', $this->_properties['value']);
        }

        $this->_properties = array();
    }
}