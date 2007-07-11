<?php
require_once 'Zend/View.php';
require_once 'Zend/View/Helper/Doctype.php';
require_once 'PHPUnit/Framework/TestCase.php';

class Zend_View_Helper_DoctypeTest extends PHPUnit_Framework_TestCase
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

    public function testDoctype()
    {
        $this->assertEquals('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">', $this->view->doctype('XHTML 1.0 Strict'));
        $this->assertEquals('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">', $this->view->doctype('XHTML 1.0 Transitional'));
        $this->assertEquals('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">', $this->view->doctype('XHTML 1.0 Frameset'));
        $this->assertEquals('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">', $this->view->doctype('XHTML 1.1'));
        $this->assertEquals('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.0//EN" "http://www.w3.org/TR/xhtml-basic/xhtml-basic10.dtd">', $this->view->doctype('XHTML Basic 1.0'));
        $this->assertEquals('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.1//EN" "http://www.w3.org/TR/xhtml-basic/xhtml-basic11.dtd">', $this->view->doctype('XHTML Basic 1.1'));
        $this->assertEquals('<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">', $this->view->doctype('XHTML Mobile 1.0'));
        $this->assertEquals('<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.1//EN" "http://www.openmobilealliance.org/tech/DTD/xhtml-mobile11.dtd">', $this->view->doctype('XHTML Mobile 1.1'));
        $this->assertEquals('<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.2//EN" "http://www.openmobilealliance.org/tech/DTD/xhtml-mobile12.dtd">', $this->view->doctype('XHTML Mobile 1.2'));
        $this->assertEquals('<!DOCTYPE svg:svg PUBLIC "-//W3C//DTD XHTML 1.1 plus MathML 2.0 plus SVG 1.1//EN" "http://www.w3.org/2002/04/xhtml-math-svg/xhtml-math-svg.dtd">', $this->view->doctype('XHTML 1.1 plus MathML 2.0 plus SVG 1.1'));
        $this->assertEquals('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">', $this->view->doctype('HTML 4.01'));
        $this->assertEquals('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">', $this->view->doctype('HTML 4.01 Transitional'));
        $this->assertEquals('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">', $this->view->doctype('HTML 4.01 Frameset'));
        $this->assertEquals('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0//EN" "http://www.w3.org/TR/REC-html40/strict.dtd">', $this->view->doctype('HTML 4.0'));
        $this->assertEquals('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">', $this->view->doctype('HTML 4.0 Transitional'));
        $this->assertEquals('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Frameset//EN" "http://www.w3.org/TR/REC-html40/frameset.dtd">', $this->view->doctype('HTML 4.0 Frameset'));
        $this->assertEquals('<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">', $this->view->doctype('HTML 3.2 Final'));
        $this->assertEquals('<!DOCTYPE html PUBLIC "-//IETF//DTD HTML 2.0//EN">', $this->view->doctype('HTML 2.0'));
    }

}