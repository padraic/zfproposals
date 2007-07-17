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
 * @version    $Id: Openid.php 58 2007-07-16 11:28:29Z padraic $
 */

/** Zend_Openid_Exception */
require_once 'Zend/Openid/Exception.php';

/** Zend_Uri */
require_once 'Zend/Uri.php';

/**
 * OpenID ancestor class acting as a version and constant holder for the
 * Consumer and Provider descendants implementing the 1.1 and 2.0
 * OpenID Authentication specifications.
 *
 * @category   Zend
 * @package    Zend_Openid
 * @copyright  Copyright (c) 2007 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Openid
{

    /**
     * Static version value; default is 2.0. If a 2.0 process is failing, we
     * may fall back to 1.1 during discovery stage. This may also be changed
     * while validating responses when the OP sends a 1.1 namespace value
     * indicating the response should be interpreted under the 1.1
     * backwards compatibility rules.
     *
     * @var float
     */
    protected static $_version = 2.0;

    /**
     * The Openid Extension Namespaces supported by the current
     * instance. These can be extended by adding namespaces using
     * the static Zend_Openid::addExtensionNamespace() method.
     *
     * @var array
     */
    protected static $_extensionNamespaces = array();

    /**
     * Maintains the current association type
     *
     * @var string
     */
    protected static $_assocType = 'HMAC-SHA256';

    /**
     * Maintains the current session type
     *
     * @var string
     */
    protected static $_sessionType = 'DH-SHA256';

    /**
     * Constants:
     * OpenID 2.0 Authentication Response Codes
     */
    const OPENID_RESPONSE_SUCCESS = 'success';
    const OPENID_RESPONSE_CANCEL = 'cancel';
    const OPENID_RESPONSE_FAILURE = 'failure';
    const OPENID_RESPONSE_SETUP_NEEDED = 'setup needed';
    const OPENID_RESPONSE_PARSE_ERROR = 'parse error';

    /**
     * Constants:
     * OpenID 1.0 XML Namespace for Yadis XRD Documents
     */
    const OPENID_XML_NAMESPACE = 'http://openid.net/xmlns/1.0';

    /**
     * Constants:
     * OpenID 2.0/1.1 HTTP Request/Response Namespace Keys
     */
    const OPENID_2_0_NAMESPACE = 'http://specs.openid.net/auth/2.0';
    const OPENID_1_1_NAMESPACE = 'http://openid.net/signon/1.1';
    const OPENID_1_0_NAMESPACE = 'http://openid.net/signon/1.0';

    /**
     * Constants:
     * OpenID 2.0 Yadis XRD Service Types
     */
    const OPENID_2_0_SERVICE_SERVER_TYPE = 'http://specs.openid.net/auth/2.0/server';
    const OPENID_2_0_SERVICE_SIGNON_TYPE = 'http://specs.openid.net/auth/2.0/signon';
    const OPENID_1_2_SERVICE_SIGNON_TYPE = 'http://openid.net/signon/1.2';
    const OPENID_1_1_SERVICE_SIGNON_TYPE = 'http://openid.net/signon/1.1';
    const OPENID_1_0_SERVICE_SIGNON_TYPE = 'http://openid.net/signon/1.0';

    /**
     * Constants:
     * Supported Association Hash Algorithms (preferred)
     */
    const OPENID_1_1_HASH_ALGORITHM = 'SHA1';
    const OPENID_2_0_HASH_ALGORITHM = 'SHA256';

    /**
     * Constants:
     * Can the client retain state between requests? One hopes so...
     */
    const OPENID_STATELESS = 'no-encryption';
    const OPENID_STATEFULL = 'encryption';


    /**
     * Constants:
     * OpenID PHP Session Namespace
     */
    const OPENID_PHP_SESSION_NAMESPACE = 'OPENID_SESSION';

    /**
     * Constants:
     * Diffie-Hellman Constants
     */
    const OPENID_DIFFIEHELLMAN_DEFAULT_PRIME = '155172898181473697471232257763715539915724801966915404479707795314057629378541917580651227423698188993727816152646631438561595825688188889951272158842675419950341258706556549803580104870537681476726513255747040765857479291291572334510643245094715007229621094194349783925984760375594985848253359305585439638443';
    const OPENID_DIFFIEHELLMAN_DEFAULT_GENERATOR = '2';
    /**
     * Base64 Encoded BTWOC Representations of the default DH values
     * A wee bit of preemptive caching
     */
    const OPENID_DIFFIEHELLMAN_DEFAULT_PRIME_BASE64 = 'ANz5OguIOXLsDhmYmsWizjEOHTdxfo2Vcbt2I3MYZuYe91ouJ4mLBX+YkcLiemOcPym2CBRYHNOyyjmG0mg3BVd9RcLn5S3IHHoXGHblzqdLFEi/368Ygo79JRnxTkXjgmY0rxlJ5bU1zIKaSDuKdiI+XUkKJX8Fvf8W8vsixYOr';
    const OPENID_DIFFIEHELLMAN_DEFAULT_GENERATOR_BASE64 = 'Ag==';

    /**
     * Constants:
     * Prefix for keys in Request Parameters
     */
    const OPENID_ARGUMENT_KEY_PREFIX = 'openid';

    /**
     * Mode; either the client supports storing state between
     * sessions (e.g. database), or it doesn't. In PHP, we really
     * should never use OpenID in stateless mode unless strictly
     * for testing purposes.
     *
     * @var string
     */
    protected static $_mode = Zend_Openid::OPENID_STATEFULL;

    /**
     * Set the current OpenID specification version we are proceeding under.
     * This can be reset during service/OP discovery in order to force a
     * fallback position to 1.1 (for those providers/aliases not yet updating
     * to 2.0 for example).
     *
     * @param float $version
     * @return void
     */
    public static function setVersion($version)
    {
        if (!in_array($version, array(1.1, 2.0))) {
            require_once 'Zend/Openid/Exception.php';
            throw new Zend_Openid_Exception('Not a valid OpenID Specification version');
        }
        if ($version == 1.1) {
            self::setAssocType('sha1');
            self::setSessionType('sha1');
        } else {
            self::setAssocType('sha256');
            self::setSessionType('sha256');
        }
        self::$_version = $version;
    }

    /**
     * Return the current operating version
     *
     * @return float
     */
    public static function getVersion()
    {
        return self::$_version;
    }

    /**
     * Set additional Extension namespaces so responses
     * may be parsed accordingly.
     *
     * @param $namespace
     * @param $uri
     * @return void
     */
    public static function addExtensionNamespace($namespace, $uri)
    {
        if (!in_array($namespace, self::$_extensionNamespaces)) {
            if (!Zend_Uri::check($uri)) {
                throw new Zend_Openid_Exception('Namespace value is not a valid URI');
            }
            self::$_extensionNamespaces[$namespace] = $uri;
            return;
        }
        return false;
    }

    /**
     * Get a specific Extension Namespace value
     *
     * @param string $namespace
     * @return string
     */
    public static function getExtensionNamespace($namespace)
    {
        if (isset(self::$_extensionNamespaces[$namespace])) {
            return self::$_extensionNamespaces[$namespace];
        }
        return null;
    }

    /**
     * Get the array of Extension Namespaces for this instance
     *
     * @return array
     */
    public static function getExtensionNamespaces()
    {
        return self::$_extensionNamespaces;
    }

    /**
     * Set a new Association Type based on SHA type
     *
     * @param string $hash
     * @return void
     */
    public static function setAssocType($hash)
    {
        $hash = strtolower($hash);
        if (!in_array($hash, array('sha1','sha256'))) {
            throw new Zend_Openid_Exception('"' . $hash . '" is an invalid hash for an Association Type');
        }
        if ($hash == 'sha1') {
            self::$_assocType = 'HMAC-SHA1';
            return;
        }
        self::$_assocType = 'HMAC-SHA256';
    }

    /**
     * Return the current Association Type preference
     *
     * @return string
     */
    public static function getAssocType()
    {
        return self::$_assocType;
    }

    /**
     * Set a new Session Type based on SHA type
     *
     * @param string $type
     * @return void
     */
    public static function setSessionType($type)
    {
        $hash = strtolower($type);
        if (!in_array($type, array('sha1','sha256', Zend_Openid::OPENID_STATELESS))) {
            throw new Zend_Openid_Exception('"' . $type . '" is an invalid hash for a Session Type');
        }
        if ($type == 'sha1') {
            self::$_sessionType = 'DH-SHA1';
            return;
        } elseif ($type == 'sha256') {
            self::$_sessionType = 'DH-SHA256';
        } else {
            self::$_sessionType = 'no-encryption';
        }
    }

    /**
     * Return the current Session Type preference
     *
     * @return string
     */
    public static function getSessionType()
    {
        return self::$_sessionType;
    }

    /**
     * Based on the current Association Type settings return a hash algorithm
     * name we may use.
     *
     * @param string $type
     * @return string|boolean
     */
    public static function getHashFromType($type = null)
    {
        if (is_null($type)) {
            $type = self::getAssocType();
        }
        if (preg_match("/SHA256$/i", $type)) {
            return 'SHA256';
        }
        if (preg_match("/SHA1$/i", $type)) {
            return 'SHA1';
        }
        return false;
    }

    /**
     * Set the client to being stateless or statefull.
     *
     * @param string $mode
     * @return void
     */
    public static function setMode($mode)
    {
        if ($mode == Zend_Openid::OPENID_STATELESS) {
            self::$_mode = Zend_Openid::OPENID_STATELESS;
        } else {
            self::$_mode = Zend_Openid::OPENID_STATEFULL;
        }
    }

    /**
     * Get current stateless|full mode
     *
     * @return string
     */
    public static function getMode()
    {
        return self::$_mode;
    }

}