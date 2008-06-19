<?php

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Zend_Oauth_AllTests::main');
}

require_once dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'TestHelper.php';

require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'OauthTest.php';
require_once 'Oauth/ConsumerTest.php';

require_once 'Oauth/Signature/PlaintextTest.php';
require_once 'Oauth/Signature/HmacTest.php';
require_once 'Oauth/Signature/RsaTest.php';

require_once 'Oauth/Request/RequestTokenTest.php';

class Zend_Oauth_AllTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Zend Framework - Zend_Oauth');

        $suite->addTestSuite('Zend_OauthTest');
        $suite->addTestSuite('Zend_Oauth_ConsumerTest');

        $suite->addTestSuite('Zend_Oauth_Signature_PlaintextTest');
        $suite->addTestSuite('Zend_Oauth_Signature_HmacTest');
        $suite->addTestSuite('Zend_Oauth_Signature_RsaTest');

        $suite->addTestSuite('Zend_Oauth_Request_RequestTokenTest');

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Zend_Oauth_AllTests::main') {
    Zend_Oauth_AllTests::main();
}
