<?php

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Zend_Oauth_AllTests::main');
}

require_once dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'TestHelper.php';

require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'TokenTest.php';

class Zend_Oauth_AllTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Zend Framework - Zend_Oauth');

        $suite->addTestSuite('Zend_Crypt_TokenTest');

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Zend_Oauth_AllTests::main') {
    Zend_Oauth_AllTests::main();
}
