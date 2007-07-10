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
 * Helper to add a doctype declaration based on the HTML standard parameter
 *
 * @category   Zend
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2007 Pádraic Brady
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_View_Helper_HtmlDoctype
{

    public function htmlDoctype($standard = 'XHTML 1.0 Transitional') {
        $doctype = null;
        switch($standard) {
            case '':
                $doctype = '';
                break;
            case '':
                $doctype = '';
                break;
            case '':
                $doctype = '';
                break;
            case '':
                $doctype = '';
                break;
            case '':
                $doctype = '';
                break;
            case '':
                $doctype = '';
                break;
            case '':
                $doctype = '';
                break;
            case '':
                $doctype = '';
                break;
            case '':
                $doctype = '';
                break;
            case '':
                $doctype = '';
                break;
            case '':
                $doctype = '';
                break;
            case '':
                $doctype = '';
                break;
            case '':
                $doctype = '';
                break;
            case '':
                $doctype = '';
                break;
            default:
                throw new Zend_View_Exception('Invalid HTML standard: ' . $standard);
        }
        return $doctype;
    }

}
