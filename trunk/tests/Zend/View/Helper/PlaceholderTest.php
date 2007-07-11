<?php
require_once 'Zend/View.php';
require_once 'Zend/View/Helper/Placeholder.php';
require_once 'PHPUnit/Framework/TestCase.php';

class Zend_View_Helper_PlaceholderTest extends PHPUnit_Framework_TestCase
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

    public function testPlaceholder()
    {
        $this->assertTrue($this->view->placeholder() instanceof Zend_View_Helper_Placeholder);

        $this->view->placeholder()->set('varName1', 'defaultValue');
        $this->assertTrue($this->view->placeholder()->has('varName1'));
        $this->assertEquals('defaultValue', $this->view->placeholder()->get('varName1'));

        $this->view->placeholder()->append('varName1', 'defaultValue');
        $this->assertTrue($this->view->placeholder()->has('varName1'));
        $this->assertEquals("defaultValue\ndefaultValue", $this->view->placeholder()->get('varName1'));
        $this->assertEquals(array('defaultValue','defaultValue'), $this->view->placeholder()->asArray('varName1'));

        $this->view->placeholder()->remove('varName1');
        $this->assertFalse($this->view->placeholder()->has('varName1'));
        $this->assertEquals(null, $this->view->placeholder()->get('varName1'));
    }

    public function testPlaceholderIndexed()
    {
        $this->assertTrue($this->view->placeholder() instanceof Zend_View_Helper_Placeholder);

        $this->view->placeholder()->set('keyName1', 'defaultValue1', 1);
        $this->view->placeholder()->set('keyName1', 'defaultValue0', 0);
        $this->assertTrue($this->view->placeholder()->has('keyName1'));
        $this->assertTrue($this->view->placeholder()->has('keyName1', 1));
        $this->assertTrue($this->view->placeholder()->has('keyName1', 0));
        $this->assertEquals('defaultValue1', $this->view->placeholder()->get('keyName1', 1));
        $this->assertEquals('defaultValue0', $this->view->placeholder()->get('keyName1', 0));

        $this->view->placeholder()->append('keyName1', 'defaultValue2');
        $this->assertTrue($this->view->placeholder()->has('keyName1', 2));

        // default _toString() behaviour for array of values (default ksort)
        $this->assertEquals("defaultValue0\ndefaultValue1\ndefaultValue2", $this->view->placeholder()->get('keyName1'));

        $this->view->placeholder()->remove('keyName1', 0);
        $this->assertFalse($this->view->placeholder()->has('varName1', 0));
        $this->assertEquals("defaultValue1\ndefaultValue2", $this->view->placeholder()->get('keyName1'));
    }

}