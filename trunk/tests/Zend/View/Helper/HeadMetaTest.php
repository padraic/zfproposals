<?php
require_once 'Zend/View.php';
require_once 'Zend/View/Helper/HeadMeta.php';
require_once 'PHPUnit/Framework/TestCase.php';

class Zend_View_Helper_HeadMetaTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $view = new Zend_View();
        $view->strictVars(true);
        $this->view = $view;
    }

    public function tearDown()
    {
        unset($this->view);
    }

    public function testHeadMeta()
    {
        $this->view->headMeta('<meta http-equiv="Content-type" content="text/html;charset=utf-8" />');
        $this->assertTrue($this->view->headMeta()->has());
        $this->assertEquals('<meta http-equiv="Content-type" content="text/html;charset=utf-8" />', $this->view->headMeta()->get());
        $this->assertEquals('<meta http-equiv="Content-type" content="text/html;charset=utf-8" />', (string) $this->view->headMeta());

        $this->view->headMeta('<meta http-equiv="Expires" content="Sat, 16 Nov 2002 00:00:01 GMT" />');
        $this->assertEquals('<meta http-equiv="Content-type" content="text/html;charset=utf-8" />' . "\n" . '<meta http-equiv="Expires" content="Sat, 16 Nov 2002 00:00:01 GMT" />', $this->view->headMeta()->get());
        $this->assertEquals('<meta http-equiv="Content-type" content="text/html;charset=utf-8" />' . "\n" . '<meta http-equiv="Expires" content="Sat, 16 Nov 2002 00:00:01 GMT" />', (string) $this->view->headMeta());

        $this->view->headMeta()->remove(null, '<meta http-equiv="Expires" content="Sat, 16 Nov 2002 00:00:01 GMT" />');
        $this->assertEquals('<meta http-equiv="Content-type" content="text/html;charset=utf-8" />', $this->view->headMeta()->get());
        $this->assertEquals('<meta http-equiv="Content-type" content="text/html;charset=utf-8" />', (string) $this->view->headMeta());
    }

}