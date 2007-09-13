<?php

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Zend_Yaml_AllTests::main');
}

require_once dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'TestHelper.php';

require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'ReaderTest.php';
require_once 'CharacterTest.php';
require_once 'ParserTest.php';

class Zend_Yaml_AllTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Zend Framework - Zend_Yaml');

        $suite->addTestSuite('Zend_Yaml_ReaderTest');
        $suite->addTestSuite('Zend_Yaml_CharacterTest');
        $suite->addTestSuite('Zend_Yaml_ParserTest');

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Zend_Yaml_AllTests::main') {
    Zend_Yaml_AllTests::main();
}
