<?php

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Zend_Feed_AllTests::main');
}

require_once dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'TestHelper.php';

require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'ReaderTest.php';
require_once 'Reader/Feed/RssTest.php';

class Zend_Feed_AllTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Zend Framework - Zend_Feed');

        $suite->addTestSuite('Zend_Feed_ReaderTest');

        // RSS - Feed Level
        $suite->addTestSuite('Zend_Feed_Reader_Feed_RssTest');

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Zend_Feed_AllTests::main') {
    Zend_Feed_AllTests::main();
}
