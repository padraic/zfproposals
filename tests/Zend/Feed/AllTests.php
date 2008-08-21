<?php

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Zend_Feed_AllTests::main');
}

require_once dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'TestHelper.php';

require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'ReaderTest.php';
require_once 'Reader/AuthorTest.php';
require_once 'Reader/Feed/RssTest.php';
require_once 'Reader/Entry/RssTest.php';
require_once 'Reader/Feed/AtomTest.php';
require_once 'Reader/Entry/AtomTest.php';

class Zend_Feed_AllTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Zend Framework - Zend_Feed');

        // Author
        $suite->addTestSuite('Zend_Feed_Reader_AuthorTest');
        // Base parent class
        $suite->addTestSuite('Zend_Feed_ReaderTest');
        // RSS - Feed Level
        $suite->addTestSuite('Zend_Feed_Reader_Feed_RssTest');
        // RSS - Item Level
        $suite->addTestSuite('Zend_Feed_Reader_Entry_RssTest');
        // ATOM - Feed Level
        $suite->addTestSuite('Zend_Feed_Reader_Feed_AtomTest');
        // ATOM - Item Level
        $suite->addTestSuite('Zend_Feed_Reader_Entry_AtomTest');

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Zend_Feed_AllTests::main') {
    Zend_Feed_AllTests::main();
}
