<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2007 Pádraic Brady
 * @version    $Id: HeadMeta.php 92 2007-07-08 14:51:24Z padraic $
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * Helper to obtain a doctype declaration based on the $standard parameter
 * which reflects the full name of the standard including version and context
 *
 * @category   Zend
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2007 Pádraic Brady
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_View_Helper_Doctype
{

    public function doctype($standard = 'XHTML 1.0 Transitional') {
        if(stripos($standard, 'xhtml') == 0) {
            return $this->_doctypeXhtml($standard);
        }
        if(stripos($standard, 'html') == 0) {
            return $this->_doctypeHtml($standard);
        }
        if(stripos($standard, 'mathml') == 0) {
            return $this->_doctypeMath($standard);
        }
        if(stripos($standard, 'svg') == 0) {
            return $this->_doctypeSvg($standard);
        }
        throw new Zend_View_Exception('Invalid standard: ' . $standard);
    }

    protected function _doctypeXhtml($standard) {
        $doctype = null;
        switch($standard) {
            case 'XHTML 1.0 Strict':
                $doctype = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
                break;
            case 'XHTML 1.0 Transitional':
                $doctype = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
                break;
            case 'XHTML 1.0 Frameset':
                $doctype = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">';
                break;
            case 'XHTML 1.1':
                $doctype = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">';
                break;
            case 'XHTML Basic 1.0':
                $doctype = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.0//EN" "http://www.w3.org/TR/xhtml-basic/xhtml-basic10.dtd">';
                break;
            case 'XHTML Basic 1.1':
                $doctype = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.1//EN" "http://www.w3.org/TR/xhtml-basic/xhtml-basic11.dtd">';
                break;
            case 'XHTML Mobile 1.0':
                $doctype = '<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">';
                break;
            case 'XHTML Mobile 1.1':
                $doctype = '<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.1//EN" "http://www.openmobilealliance.org/tech/DTD/xhtml-mobile11.dtd">';
                break;
            case 'XHTML Mobile 1.2':
                $doctype = '<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.2//EN" "http://www.openmobilealliance.org/tech/DTD/xhtml-mobile12.dtd">';
                break;
            case 'XHTML 1.1 plus MathML 2.0 plus SVG 1.1':
                $doctype = '<!DOCTYPE svg:svg PUBLIC "-//W3C//DTD XHTML 1.1 plus MathML 2.0 plus SVG 1.1//EN" "http://www.w3.org/2002/04/xhtml-math-svg/xhtml-math-svg.dtd">';
                break;
            default:
                throw new Zend_View_Exception('Invalid XHTML standard: ' . $standard);
        }
        return $doctype;
    }

    protected function _doctypeHtml($standard) {
        $doctype = null;
        switch($standard) {
            case 'HTML 4.01':
                $doctype = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">';
                break;
            case 'HTML 4.01 Transitional':
                $doctype = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
                break;
            case 'HTML 4.01 Frameset':
                $doctype = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">';
                break;
            case 'HTML 4.0':
                $doctype = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0//EN" "http://www.w3.org/TR/REC-html40/strict.dtd">';
                break;
            case 'HTML 4.0 Transitional':
                $doctype = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">';
                break;
            case 'HTML 4.0 Frameset':
                $doctype = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Frameset//EN" "http://www.w3.org/TR/REC-html40/frameset.dtd">';
                break;
            case 'HTML 3.2':
                $doctype = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">';
                break;
            case 'HTML 2.0':
                $doctype = '<!DOCTYPE html PUBLIC "-//IETF//DTD HTML 2.0//EN">';
                break;
            default:
                throw new Zend_View_Exception('Invalid HTML standard: ' . $standard);
        }
        return $doctype;
    }

    protected function _doctypeMath($standard) {
        $doctype = null;
        switch($standard) {
            case 'MathML 2.0':
                $doctype = '<!DOCTYPE math PUBLIC "-//W3C//DTD MathML 2.0//EN" "http://www.w3.org/TR/MathML2/dtd/mathml2.dtd">';
                break;
            case 'MathML 1.01':
                $doctype = '<!DOCTYPE math SYSTEM "http://www.w3.org/Math/DTD/mathml1/mathml.dtd">';
                break;
            case 'MathML 2.0 plus SVG 1.1':
                $doctype = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1 plus MathML 2.0 plus SVG 1.1//EN" "http://www.w3.org/2002/04/xhtml-math-svg/xhtml-math-svg.dtd">';
                break;
            default:
                throw new Zend_View_Exception('Invalid MathML standard: ' . $standard);
        }
        return $doctype;
    }

    protected function _doctypeSvg($standard) {
        $doctype = null;
        switch($standard) {
            case 'SVG 1.0':
                $doctype = '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.0//EN" "http://www.w3.org/TR/2001/REC-SVG-20010904/DTD/svg10.dtd">';
                break;
            case 'SVG 1.1 Full':
                $doctype = '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">';
                break;
            case 'SVG 1.1 Basic':
                $doctype = '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1 Basic//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11-basic.dtd">';
                break;
            case 'SVG 1.1 Tiny':
                $doctype = '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1 Tiny//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11-tiny.dtd">';
                break;
            default:
                throw new Zend_View_Exception('Invalid SVG standard: ' . $standard);
        }
        return $doctype;
    }

}
