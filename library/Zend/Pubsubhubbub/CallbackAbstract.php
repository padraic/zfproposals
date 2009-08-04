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
 * to padraic dot brady at yahoo dot com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Pubsubhubbub
 * @copyright  Copyright (c) 2009 Padraic Brady
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @see Zend_Pubsubhubbub_CallbackInterface
 */
require_once 'Zend/Pubsubhubbub/CallbackInterface.php';

/**
 * @see Zend_Pubsubhubbub_HttpResponse
 */
require_once 'Zend/Pubsubhubbub/HttpResponse.php';

/**
 * @category   Zend
 * @package    Zend_Pubsubhubbub
 * @copyright  Copyright (c) 2009 Padraic Brady
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Pubsubhubbub_CallbackAbstract
    implements Zend_Pubsubhubbub_CallbackInterface
{

    /**
     * An instance of Zend_Pubsubhubbub_StorageInterface used to background
     * save any verification tokens associated with a subscription or other.
     *
     * @var Zend_Pubsubhubbub_StorageInterface
     */
    protected $_storage = null;

    /**
     * An instance of a class handling Http Responses. This is implemented in
     * Zend_Pubsubhubbub_HttpResponse which shares an unenforced interface with
     * (i.e. not inherited from) Zend_Controller_Response_Http.
     *
     * @var Zend_Pubsubhubbub_HttpResponse|Zend_Controller_Response_Http
     */
    protected $_httpResponse = null;

    /**
     * Send the response, including all headers.
     * If you wish to handle this via Zend_Controller, use the getter methods
     * to retrieve any data needed to be set on your HTTP Response object, or
     * simply give this object the HTTP Response instance to work with for you!
     *
     * @return void
     */
    public function sendResponse()
    {
        $this->getHttpResponse()->sendResponse();
    }

    /**
     * Sets an instance of Zend_Pubsubhubbub_StorageInterface used to background
     * save any verification tokens associated with a subscription or other.
     *
     * @param Zend_Pubsubhubbub_StorageInterface $storage
     */
    public function setStorage(Zend_Pubsubhubbub_StorageInterface $storage)
    {
        $this->_storage = $storage;
    }

    /**
     * Gets an instance of Zend_Pubsubhubbub_StorageInterface used to background
     * save any verification tokens associated with a subscription or other.
     *
     * @return Zend_Pubsubhubbub_StorageInterface
     */
    public function getStorage()
    {
        if ($this->_storage === null) {
            require_once 'Zend/Pubsubhubbub/Exception.php';
            throw new Zend_Pubsubhubbub_Exception('No storage object has been'
            . ' set that implements Zend_Pubsubhubbub_StorageInterface');
        }
        return $this->_storage;
    }

    /**
     * An instance of a class handling Http Responses. This is implemented in
     * Zend_Pubsubhubbub_HttpResponse which shares an unenforced interface with
     * (i.e. not inherited from) Zend_Controller_Response_Http.
     *
     * @param Zend_Pubsubhubbub_HttpResponse|Zend_Controller_Response_Http $httpResponse
     */
    public function setHttpResponse($httpResponse)
    {
        if (!is_object($httpResponse)
        || (!$httpResponse instanceof Zend_Pubsubhubbub_HttpResponse
        && !$httpResponse instanceof Zend_Controller_Response_Http)) {
            require_once 'Zend/Pubsubhubbub/Exception.php';
            throw new Zend_Pubsubhubbub_Exception('HTTP Response object must'
            . ' implement one of Zend_Pubsubhubbub_HttpResponse or'
            . ' Zend_Controller_Response_Http');
        }
        $this->_httpResponse = $httpResponse;
    }

    /**
     * An instance of a class handling Http Responses. This is implemented in
     * Zend_Pubsubhubbub_HttpResponse which shares an unenforced interface with
     * (i.e. not inherited from) Zend_Controller_Response_Http.
     *
     * @return Zend_Pubsubhubbub_HttpResponse|Zend_Controller_Response_Http
     */
    public function getHttpResponse()
    {
        if ($this->_httpResponse === null) {
            $this->_httpResponse = new Zend_Pubsubhubbub_HttpResponse;
        }
        return $this->_httpResponse;
    }

    /**
     * Attempt to detect the callback URL (specifically the path forward)
     */
    protected function _detectCallbackUrl()
    {
        $callbackUrl = '';
        if (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
            $callbackUrl = $_SERVER['HTTP_X_REWRITE_URL'];
        } elseif (isset($_SERVER['REQUEST_URI'])) {
            $callbackUrl = $_SERVER['REQUEST_URI'];
            $scheme = 'http';
            if ($_SERVER['HTTPS'] == 'on') {
                $scheme = 'https';
            }
            $schemeAndHttpHost = $scheme . '://' . $this->_getHttpHost();
            if (strpos($callbackUrl, $schemeAndHttpHost) === 0) {
                $callbackUrl = substr($callbackUrl, strlen($schemeAndHttpHost));
            }
        } elseif (isset($_SERVER['ORIG_PATH_INFO'])) {
            $callbackUrl= $_SERVER['ORIG_PATH_INFO'];
            if (!empty($_SERVER['QUERY_STRING'])) {
                $callbackUrl .= '?' . $_SERVER['QUERY_STRING'];
            }
        }
        return $callbackUrl;
    }

    /**
     * Get the HTTP host
     *
     * @return string
     */
    protected function _getHttpHost()
    {
        if (!empty($_SERVER['HTTP_HOST'])) {
            return $_SERVER['HTTP_HOST'];
        }
        $scheme = 'http';
        if ($_SERVER['HTTPS'] == 'on') {
            $scheme = 'https';
        }
        $name = $_SERVER['SERVER_NAME'];
        $port = $_SERVER['SERVER_PORT'];
        if (($scheme == 'http' && $port == 80)
        || ($scheme == 'https' && $port == 443)) {
            return $name;
        } else {
            return $name . ':' . $port;
        }
    }

    /**
     * Retrieve a Header value from either $_SERVER or Apache
     *
     * @param string $header
     */
    protected function _getHeader($header)
    {
        $temp = strtoupper(str_replace('-', '_', $header));
        if (!empty($_SERVER[$temp])) {
            return $_SERVER[$temp];
        }
        $temp = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
        if (!empty($_SERVER[$temp])) {
            return $_SERVER[$temp];
        }
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (!empty($headers[$header])) {
                return $headers[$header];
            }
        }
        return false;
    }

    /**
     * Return the raw body of the request
     *
     * @return string|false Raw body, or false if not present
     */
    protected function _getRawBody()
    {
        $body = file_get_contents('php://input');
        if (strlen(trim($body)) == 0 && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
            $body = $GLOBALS['HTTP_RAW_POST_DATA'];
        }
        if (strlen(trim($body)) > 0) {
            return $body;
        }
        return false;
    }

}
