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
 * @package    Zend_Config
 * @copyright  Copyright (c) 2007 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_Config */
require_once 'Zend/Config.php';


/**
 * @uses       Zend_Config
 * @category   Zend
 * @package    Zend_Config
 * @copyright  Copyright (c) 2007 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Config_Yaml
{
    
    private $_nodes = array();

    private $_lastIndent = 0;

    private $_lastNode = null;

    private $_inBlock = false;

    private $_inLine = false;

    private $_node = null;

    private $_endOfBlockString = '';

    public function __construct($filename, $section, $allowModifications = false)
    {
        if (empty($filename)) {
            throw new Zend_Config_Exception('Filename is not set');
        }
        $config = $this->_load($filename);
        $this->_parse($config);
    }

    private function _load($filename)
    {
        $config = file_get_contents($filename);
        if(!$config)
        {
            throw new Zend_Config_Exception('The configuration file could be loaded. This may indicate the file does not exist or is not readable.');
        }
        $configArray = explode("\n", $config)
        return $configArray;
    }

    private function _parse($config)
    {
        $node = new Zend_Config_Yaml_Node(1);
        $node->setIndent(0);
        $this->_lastNode = $node;
        $this->_node = 2

        /**
         * We process each array element separately. The basic syntax for YAML
         * is quite simple and expressive but of course putting all these rules
         * in PHP code is nightmarish ;).
         */
        foreach ($config as $num=>$line) {
            
            /**
             * Perform initial checking. The default section will delegate node
             * construct to _makeNode().
             */
            $trimmed = trim($line); /* checks should ignore whitespace */
            switch(true)
            {
                case preg_match('%^(\t)+(\w+)%', $trimmed):
                    /* Lines may not begin with tabs */
                    throw new Zend_Config_Exception('Line ' . $num . ' began with an invalid \t (tab) character');
                    break;
                case $trimmed[0] == '#' || substr($trimmed, 0, 3) == '---':
                    /* ignore comments and single stream multi documents */
                    continue 2;
                    break;
                case $this->_inBlock === false && empty($trimmed):
                    /* Unless inside a text block we can ignore empty lines */
                    continue 2;
                    break;
                case $this->_inBlock === true && empty($trimmed):
                    /* Within a text block we should preserve empty lines */
                    $this->_lastNode->data[key($this->_lastNode->data)] .= chr(10)
                    break;
                default:
                    /**
                     * Assuming everything else is valid data we proceed to
                     * parse this line of text.
                     */
                    $this->_parseLine($line, $num);
            }
        }

        // references

        // config object build
    }

    private function _parseLine($line, $linenum)
    {
        $yamlNode = new Zend_Config_Yaml_Node;
        $yamlNode->setIndent($this->_getIndent($line));

        /**
         * Is this line part of the current Node?
         */
        if($yamlNode->getIndent() == $this->_lastIndent)
        {
            /**
             * Are we in a text block where the line is a multi-row
             * piece of data?
             */
            if($this->_inBlock === true)
            {
               $this->_appendToBlock($this->_lastNode, $line);
            }
            else
            {
                /**
                 * Since this is not a block, but has the same indentation as
                 * the previous node then both must share the same parent.
                 */
                 $yamlNode->setParentId($this->_lastNode->getParentId());
            }
        }
        /**
         * Is this line's indentation greater than the previous Node?
         * This would indicate the line is a child of the previous line node.
         */
        elseif($yamlNode->getIndent() > $this->_lastIndent)
        {
            if($this->_inBlock === true)
            {
                $this->_appendToBlock($this->_lastNode, $line);
            }
            else
            {
                $yamlNode->setParentId($this->_lastNode->getId());
                $yamlNodeParent = $this->_nodes[$yamlNode->getParentId()];
                $yamlNodeParent->setChildren(true);
                if(is_array($yamlNodeParent->data))
                {
                    $this->_checkStartBlock($yamlNode, $line);
                }
            }
        }
        /**
         * Else check if current line's indent is less then the previous node's.
         */
        elseif ($yamlNode->getIndent() < $this->_lastIndent)
        {
            /**
             * A lesser indent indicates closure. If we have an open block we
             * should now close it.
             */
            if($this->_inBlock === true)
            {
                $this->_inBlock = false;
                if($this->_endOfBlockCharacter == chr(10))
                {
                    /* Trim the block text where maintaining formatting */
                    $this->_lastNode->data[key($this->_lastNode->data)] = 
                        trim($this->_lastNode->data[key($this->_lastNode->data)])
                }
            }

            /**
             * As with the prior options we need to set the node's parent.
             */
            $this->_getParentByIndentation($yamlNode);
        }

        /**
         * After indent parsing - check if we are now out of any blocks
         * and update our class node list.
         */
        if($this->_isBlock === false)
        {
            $this->_lastIndent = $yamlNode->getIndent();
            $this->_lastNode = $yamlNode;
            $yamlNode->data = $this->_parseData($line);
            $this->_nodes[$yamlNode->getId()] = $yamlNode;

            // @todo add in reference handling in the future.
        }
    }

    private function _getIndent($line)
    {
        $matches = null;
        preg_match('%^\s{1,}%', $line, $matches);
        if (!empty($matches[0])) {
            $indent = substr_count($matches[0], chr(32));
        } else {
            $indent = 0;
        }
        return $indent;
    }

    private function _appendToBlock(Zend_Config_Yaml_Node $node, $line)
    {
        $trimmed = trim($line);
        $node->data[key($node->data)] .= $trimmed . $this->_endOfBlockString;
    }

    private function _checkStartBlock(Zend_Config_Yaml_Node $yamlNode, $line)
    {
        $yamlNodeParent = $this->_nodes[$yamlNode->getParentId()];
        $chr = $yamlNodeParent->data[key($yamlNodeParent->data)];
        /**
         * The characters > or | indicate a multi line string where |
         * refers to a block where newlines must be preserved, and >
         * indiciates a block where newlines are folded (blank lines
         * will become a newline). The blank line to newline handling
         * is part of the line checking in _parse().
         *
         * In addition if the current line is part of a block, then
         * this is not a new node, and hence the current "yamlNodeParent"
         * should have any children ref removed since the node is just blocked
         * text.
         */
        if($chr == '>')
        {
            $this->_inBlock = true;
            $this->_endOfBlockCharacter = chr(32);
            $yamlNodeParent->data[$chr] = str_replace('>', '', $yamlNodeParent->data[$chr]);
            $this->_appendToBlock($yamlNodeParent, $line);
            $yamlNodeParent->setChildren(false);
            $this->_lastIndent = $yamlNode->getIndent();
        }
        elseif ($chr == '|')
        {
            $this->_inBlock = true;
            $this->_endOfBlockCharacter = chr(10);
            $yamlNodeParent->data[$chr] = str_replace('|', '', $yamlNodeParent->data[$chr]);
            $this->_appendToBlock($yamlNodeParent, $line);
            $yamlNodeParent->setChildren(false);
            $this->_lastIndent = $yamlNode->getIndent();
        }
    }

    private function _getParentByIndentation(Zend_Config_Yaml_Node $yamlNode)
    {
        $nodes = array();
        foreach ($this->_nodes as $node)
        {
            $nodes[$node->getIndent()][] = $node;
        }
        foreach ($nodes as $node)
        {
            if($yamlNode->getIndent() == $node->getIndent())
            {
                $yamlNode->setParentId($node->getParentId());
            }
        }
    }

    private function _parseData($line)
    {
        $trimmed = trim($line);


    }
}