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
 * @see Zend_Http_Client
 */
require_once 'Zend/Http/Client.php';

/**
 * @see Zend_Uri
 */
require_once 'Zend/Uri.php';

/**
 * @see Zend_Version
 */
require_once 'Zend/Version.php';

/**
 * @category   Zend
 * @package    Zend_Pubsubhubbub
 * @copyright  Copyright (c) 2009 Padraic Brady
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Pubsubhubbub
{

    /**
     * Singleton instance if required of the HTTP client
     *
     * @var Zend_Http_Client
     */
    protected static $httpClient = null;

    /**
     * Allows the external environment to make Zend_Oauth use a specific
     * Client instance.
     *
     * @param Zend_Http_Client $httpClient
     * @return void
     */
    public static function setHttpClient(Zend_Http_Client $httpClient)
    {
        self::$httpClient = $httpClient;
    }

    /**
     * Return the singleton instance of the HTTP Client. Note that
     * the instance is reset and cleared of previous parameters and
     * Authorization header values.
     *
     * @return Zend_Http_Client
     */
    public static function getHttpClient()
    {
        if (!isset(self::$httpClient)):
            self::$httpClient = new Zend_Http_Client;
        else:
            self::$httpClient->resetParameters();
        endif;
        return self::$httpClient;
    }

    /**
     * Simple mechanism to delete the entire singleton HTTP Client instance
     * which forces an new instantiation for subsequent requests.
     *
     * @return void
     */
    public static function clearHttpClient()
    {
        self::$httpClient = null;
    }

}
