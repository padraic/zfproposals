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
 * @package    Zend_Openid
 * @copyright  Copyright (c) 2007 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/** Zend_Uri */
require_once 'Zend/Uri.php';

/** Zend_Openid */
require_once 'Zend/Openid.php';

 /**
 * HTML Discovery takes place for OpenID 1.1, or when an OpenID 2.0 Yadis discovery
 * cycle fails. Should HTML Discovery fail to locate the required value key
 * data from the HTML document when using OpenID 2.0 we MUST fall back to OpenID 1.1
 * and override the current settings.
 *
 * @category   Zend
 * @package    Zend_Openid
 * @copyright  Copyright (c) 2007 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Openid_DiscoveryHtml
{
    
    /**
     * The response object containing the HTML body we will
     * parse for and OpenID service data
     *
     * @var Zend_Http_Response
     */
    protected $_response = null;

    /**
     * Set the response object upon which to perform HTML discovery
     *
     * @param Zend_Http_Response $response
     */
    public function setResponse(Zend_Http_Response $response)
    {
        $this->_response = $response;
    }

    /**
     * Perform HTML Discovery either on the existing Response body
     * or by requesting the optional URI parameter to get HTML content
     * on which to perform
     *
     * @param string $uri
     * @return array|boolean
     */
    public function discover($uri = null)
    {
        if (isset($this->_response)) {
            $body = $this->_response->getBody();
        } else {
            if (!isset($uri) || !Zend_Uri::check($uri)) {
                throw new Exception();
            }
            $body = $this->_get($uri);
        }
        $result = $this->parse($body);
        return $result;
    }

    /**
     * Note: This method may transparently downgrade the current OpenID version to
     * 1.1 in order to attempt a backwards compatible discovery of the Identity
     * Provider. This will in turn force a 1.1 style authentication without
     * programmer interference.
     *
     * Parse an HTML document. This will filter out the required HTML RELs and
     * return their relevant HREF values. It allows for invalid XHTML to a degree and
     * will cope with the example given at
     * {@link http://openid.net/specs/openid-authentication-2_0-11.html#anchor51}
     *
     * @param string $content
     * @return array|bool
     */
    public function parse($content)
    {   
        $validity = true;
        $result = array();

        $html = new DOMDocument();
        $html->loadHTML($content);
        $head = $html->getElementsByTagName('head');
        if ($head->length == 0) {
            require_once 'Zend/Openid/Exception.php';
            throw new Zend_Openid_Exception('Unable to complete HTML Service Discovery, the user URI points to an HTML page without a HEAD element');
        }

        $links = $head->item(0)->getElementsByTagName('link');
        if ($links->length == 0) {
            require_once 'Zend/Openid/Exception.php';
            throw new Zend_Openid_Exception('Unable to complete HTML Service Discovery, the user URI points to an HTML page without LINK elements inside a HEAD element');
        }

        $provider = null;
        $local_id = null;
        if (Zend_Openid::getVersion() == 2.0) {
            foreach ($links as $link) {
                $rel = strtolower($link->getAttribute('rel'));
                if (is_null($provider) && preg_match("/openid2.provider/i", $rel)) {
                    $provider = $link;
                } elseif (is_null($local_id) && preg_match("/openid2.local_id/i", $rel)) {
                    $local_id = $link;
                }
            }
            if (!is_null($provider)) {
                $return = array();
                $return['OpEndpoint'] = $provider->getAttribute('href');
                if (!is_null($local_id)) {
                    $return['LocalId'] = $local_id->getAttribute('href');
                }
                return $return;
            } else {
                Zend_Openid::setVersion(1.1);
            }
        }

        /**
         * At this point, if OpenID 2.0 was originally enabled, it is now
         * disabled and we have entered the backup OpenID 1.1 mode
         */

        // seems a bit smelly as the last block is almost the same...
        $server = null;
        $delegate = null;
        if (Zend_Openid::getVersion() !== 2.0) {
            foreach ($links as $link) {
                $rel = strtolower($link->getAttribute('rel'));
                if (is_null($provider) && preg_match("/openid.server/i", $rel)) {
                    $server = $link;
                } elseif (is_null($local_id) && preg_match("/openid.delegate/i", $rel)) {
                    $delegate = $link;
                }
            }
            if (!is_null($server)) { // note: local_id is optional
                $return = array();
                $return['OpEndpoint'] = $server->getAttribute('href');
                if (!is_null($delegate)) {
                    $return['LocalId'] = $delegate->getAttribute('href');
                }
            }
            return $return;
        }

        return false;
    }

    /**
     * Form a request to fetch the user's URI in a GET request. The resulting
     * response body is returned for parsing.
     *
     * @param string $uri
     * @return string
     */
    private function _get($uri)
    {
        $client = new Zend_Http_Client;
        $client->setUri($uri);
        $client->setMethod('GET');
        $response = $client->request();
        if (!$response->isSuccessful()) {
            require_once 'Zend/Openid/Request/Exception.php';
            throw new Zend_Openid_Request_Exception('Unable to recover HTML from the user identity URI on which to perform service discovery: ' . $response->getStatus() . ' ' . $response->getMessage());
        }
        return $response->getBody();
    }

}