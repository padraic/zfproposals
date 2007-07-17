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
 * @subpackage Openid
 * @copyright  Copyright (c) 2007 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Consumer.php 17 2007-06-19 23:03:41Z padraic $
 */

/** Zend_Uri */
require_once 'Zend/Uri.php';

/** Zend_Openid */
require_once 'Zend/Openid.php';

 /**
 * HTML Discovery takes place for OpenID 1.1, or when an OpenID 2.0 Yadis discovery
 * cycle fails. Should HTML Discovery fail to locate the required "openid2.provider"
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

    protected $_response = null;

    public function setResponse(Zend_Http_Response $response)
    {
        $this->_response = $response;
    }

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
        $openid2_provider_link = "%(<link[^>]*rel=([\"]{0,1}[\. a-zA-Z]*)openid2\.provider([\. a-zA-Z]*[\"]{0,1})[^>]*>)%i";
        $openid2_localid_link = "%(<link[^>]*rel=([\"]{0,1}[\. a-zA-Z]*)openid2\.local_id([\. a-zA-Z]*[\"]{0,1})[^>]*>)%i";
        $openid1_server_link = "%(<link[^>]*rel=([\"]{0,1}[\. a-zA-Z2]*)openid\.server([\. a-zA-Z2]*[\"]{0,1})[^>]*>)%i";
        $openid1_delegate_link = "%(<link[^>]*rel=([\"]{0,1}[\. a-zA-Z2_]*)openid\.delegate([\. a-zA-Z2]*[\"]{0,1})[^>]*>)%i";
        $openid_href_value = "%href=([\"]{0,1})([^\"]+)([\"]{0,1})%i";
        
        $validity = true;
        $result = array();

        if (Zend_Openid::getVersion() == '2.0') {
            preg_match($openid2_provider_link, $content, $array);
            preg_match($openid_href_value, $array[0], $href);
            $provider = $href[2];
            if (!Zend_Uri::check($provider)) {
                $validity = false;
            }
            if ($validity === true) {
                preg_match($openid2_localid_link, $content, $array);
                preg_match($openid_href_value, $array[0], $href);
                $local_id = $href[2];
                if (!Zend_Uri::check($local_id)) {
                    $validity = false;
                }
            }
            if ($validity === false) {
                Zend_Openid::setVersion('1.1');
            } else {
                $result = array(
                    'provider' => $provider,
                    'local_id' => $local_id
                );
                return $result;
            }
        }

        /**
         * At this point, if OpenID 2.0 was originally enabled, it is now
         * disabled and we have entered the backup OpenID 1.1 mode
         */
        
        if (Zend_Openid::getVersion() == '1.1') {
            preg_match($openid1_server_link, $content, $array);
            preg_match($openid_href_value, $array[0], $href);
            $server = $href[2];
            if (!Zend_Uri::check($server)) {
                $validity = false;
            }
            if ($validity === true) {
                preg_match($openid1_delegate_link, $content, $array);
                preg_match($openid_href_value, $array[0], $href);
                $delegate = $href[2];
                if (!Zend_Uri::check($delegate)) {
                    $validity = false;
                }
            }
            if ($validity === false) {
                return false;
            } else {
                $result = array(
                    'server' => $server,
                    'delegate' => $delegate
                );
                return $result;
            }
        }

        return false;
    }

    private function _get($uri)
    {
        $client = new Zend_Http_Client;
        $client->setUri($uri);
        $client->setMethod('GET');
        $response = $client->request();
        if (!$response->isSuccessful()) {
            require_once 'Zend/Service/Openid/Request/Exception.php';
            throw new Zend_Openid_Request_Exception('Invalid response to OpenID association received: ' . $response->getStatus() . ' ' . $response->getMessage());
        }
        return $response->getBody();
    }

}