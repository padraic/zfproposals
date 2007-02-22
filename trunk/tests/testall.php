<?php

/*
 * Keep code clean for E_ALL
 * We omit E_STRICT until a PHP5 only SimpleTest
 * library is released.
 */
error_reporting(E_ALL);


/*
 * Add relevant directories to the include_path
 * and omit the current include_path to prevent
 * cross polination of classes.
 */
set_include_path(
	'.' . PATH_SEPARATOR
	. realpath('./library/simpletest') . PATH_SEPARATOR
	. realpath('..')  . DIRECTORY_SEPARATOR . 'library'
);

define('TEST_ROOT', dirname(__FILE__));

/*
 * Require the basic SimpleTest classes
 */
require_once 'unit_tester.php';
require_once 'mock_objects.php';
require_once 'reporter.php';

/*
 * Add Test files to the Group Test
 */
$test = new GroupTest('GroupTest: Firiba Auth_Openid and Math_BigInteger Library Segment');
//$test->addTestFile('Quantum/Test_Db.php');

/*
 * Setup reporters for both CLI and browser
 * interfaces. Can use one or the other.
 * @todo Add a selective reporter for single test runs
 */
if(SimpleReporter::inCli())
{
	exit ($test->run(new TextReporter()) ? 0 : 1);
}
$test->run(new HtmlReporter());