<?php

require_once 'Zend/Feed/Reader/Feed.php';

/**
 * Interpretive class for Zend_Feed which interprets incoming
 * Zend_Feed_Abstract objects and presents a common unified API for all RSS
 * and Atom versions.
 *
 * @copyright 2007-2008 Pádraic Brady (http://blog.astrumfutura.com)
 */
class Zend_Feed_Reader_Feed_Atom extends Zend_Feed_Reader_Feed
{

    public function getContent()
    {
    }

    public function getTitle()
    {
    }

}