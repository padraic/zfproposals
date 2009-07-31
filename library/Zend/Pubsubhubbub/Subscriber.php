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
 * @see Zend_Pubsubhubbub
 */
require_once 'Zend/Pubsubhubbub.php';

/**
 * @category   Zend
 * @package    Zend_Pubsubhubbub
 * @copyright  Copyright (c) 2009 Padraic Brady
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Pubsubhubbub_Subscriber
{

    /**
     * An array of URLs for all Hub Servers to subscribe/unsubscribe.
     *
     * @var array
     */
    protected $_hubUrls = array();

    /**
     * An array of optional parameters to be included in any
     * (un)subscribe requests.
     *
     * @var array
     */
    protected $_parameters = array();

    /**
     * The URL of the topic (Rss or Atom feed) which is the subject of
     * our current intent to subscribe to/unsubscribe from updates from
     * the currently configured Hub Servers.
     *
     * @var string
     */
    protected $_topicUrl = '';

    /**
     * The URL Hub Servers must use when communicating with this Subscriber
     *
     * @var string
     */
    protected $_callbackUrl = '';

    /**
     * The number of seconds for which the subscriber would like to have the
     * subscription active. Defaults to specified Hub default of 2592000
     * seconds (30 days) if not supplied.
     *
     * @var int
     */
    protected $_leaseSeconds = 2592000;

    /**
     * Constructor; accepts an array or Zend_Config instance to preset
     * options for the Subscriber without calling all supported setter
     * methods in turn.
     *
     * @param array|Zend_Config $options Options array or Zend_Config instance
     */
    public function __construct($config = null)
    {
        if (!is_null($config)) {
            $this->setConfig($config);
        }
    }

    /**
     * Process any injected configuration options
     *
     * @param array|Zend_Config $options Options array or Zend_Config instance
     */
    public function setConfig($config)
    {
        if ($config instanceof Zend_Config) {
            $config = $config->toArray();
        } elseif (!is_array($config)) {
            require_once 'Zend/Pubsubhubbub/Exception.php';
            throw new Zend_Pubsubhubbub_Exception('Array or Zend_Config object'
            . 'expected, got ' . gettype($config));
        }
        if (array_key_exists('hubUrls', $config)) {
            $this->addHubUrls($config['hubUrls']);
        }
        if (array_key_exists('callbackUrl', $config)) {
            $this->setCallbackUrl($config['callbackUrl']);
        }
        if (array_key_exists('topicUrl', $config)) {
            $this->setTopicUrl($config['topicUrl']);
        }
        if (array_key_exists('leaseSeconds', $config)) {
            $this->setLeaseSeconds($config['leaseSeconds']);
        }
        if (array_key_exists('parameters', $config)) {
            $this->setParameters($config['parameters']);
        }
    }

    /**
     * Set the topic URL (RSS or Atom feed) to which the intended (un)subscribe
     * event will relate
     *
     * @param string $url
     */
    public function setTopicUrl($url)
    {
        if (empty($url) || !is_string($url) || !Zend_Uri::check($url)) {
            require_once 'Zend/Pubsubhubbub/Exception.php';
            throw new Zend_Pubsubhubbub_Exception('Invalid parameter "url"'
                .' of "' . $url . '" must be a non-empty string and a valid'
                .'URL');
        }
        $this->_topicUrl = $url;
    }

    /**
     * Set the topic URL (RSS or Atom feed) to which the intended (un)subscribe
     * event will relate
     *
     * @return string
     */
    public function getTopicUrl()
    {
        return $this->_topicUrl;
    }

    /**
     * Set the number of seconds for which any subscription will remain valid
     *
     * @param int $seconds
     */
    public function setLeaseSeconds($seconds)
    {
        $seconds = intval($seconds);
        if ($seconds <= 0) {
            require_once 'Zend/Pubsubhubbub/Exception.php';
            throw new Zend_Pubsubhubbub_Exception('Expected lease seconds'
            . ' must be an integer greater than zero');
        }
        $this->_leaseSeconds = $seconds;
    }

    /**
     * Get the number of lease seconds on subscriptions
     *
     * @return int
     */
    public function getLeaseSeconds()
    {
        return $this->_leaseSeconds;
    }

    /**
     * Get the callback URL to be used by Hub Servers when communicating with
     * this Subscriber
     *
     * @param string $url
     */
    public function setCallbackUrl($url)
    {
        if (empty($url) || !is_string($url) || !Zend_Uri::check($url)) {
            require_once 'Zend/Pubsubhubbub/Exception.php';
            throw new Zend_Pubsubhubbub_Exception('Invalid parameter "url"'
                .' of "' . $url . '" must be a non-empty string and a valid'
                .'URL');
        }
        $this->_callbackUrl = $url;
    }

    /**
     * Get the callback URL to be used by Hub Servers when communicating with
     * this Subscriber
     *
     * @return string
     */
    public function getCallbackUrl()
    {
        return $this->_callbackUrl;
    }

    /**
     * Add a Hub Server URL supported by Publisher
     *
     * @param string $url
     */
    public function addHubUrl($url)
    {
        if (empty($url) || !is_string($url) || !Zend_Uri::check($url)) {
            require_once 'Zend/Pubsubhubbub/Exception.php';
            throw new Zend_Pubsubhubbub_Exception('Invalid parameter "url"'
                .' of "' . $url . '" must be a non-empty string and a valid'
                .'URL');
        }
        $this->_hubUrls[] = $url;
    }

    /**
     * Add an array of Hub Server URLs supported by Publisher
     *
     * @param array $urls
     */
    public function addHubUrls(array $urls)
    {
        foreach ($urls as $url) {
            $this->addHubUrl($url);
        }
    }

    /**
     * Remove a Hub Server URL
     *
     * @param string $url
     */
    public function removeHubUrl($url)
    {
        if (!in_array($url, $this->getHubUrls())) {
            return;
        }
        $key = array_search($url, $this->_hubUrls);
        unset($this->_hubUrls[$key]);
    }

    /**
     * Return an array of unique Hub Server URLs currently available
     *
     * @return array
     */
    public function getHubUrls()
    {
        $this->_hubUrls = array_unique($this->_hubUrls);
        return $this->_hubUrls;
    }

    /**
     * Add an optional parameter to the (un)subscribe requests
     *
     * @param string $name
     * @param string|null $value
     */
    public function setParameter($name, $value = null)
    {
        if (is_array($name)) {
            $this->setParameters($name);
            return;
        }
        if (empty($name) || !is_string($name)) {
            require_once 'Zend/Pubsubhubbub/Exception.php';
            throw new Zend_Pubsubhubbub_Exception('Invalid parameter "name"'
                .' of "' . $name . '" must be a non-empty string');
        }
        if ($value === null) {
            $this->removeParameter($name);
            return;
        }
        if (empty($value) || (!is_string($value) && !is_null($value))) {
            require_once 'Zend/Pubsubhubbub/Exception.php';
            throw new Zend_Pubsubhubbub_Exception('Invalid parameter "value"'
                .' of "' . $value . '" must be a non-empty string');
        }
        $this->_parameters[$name] = $value;
    }

    /**
     * Add an optional parameter to the (un)subscribe requests
     *
     * @param string $name
     * @param string|null $value
     */
    public function setParameters(array $parameters)
    {
        foreach ($parameters as $name => $value) {
            $this->setParameter($name, $value);
        }
    }

    /**
     * Remove an optional parameter for the (un)subscribe requests
     *
     * @param string $name
     */
    public function removeParameter($name)
    {
        if (empty($name) || !is_string($name)) {
            require_once 'Zend/Pubsubhubbub/Exception.php';
            throw new Zend_Pubsubhubbub_Exception('Invalid parameter "name"'
                .' of "' . $name . '" must be a non-empty string');
        }
        if (array_key_exists($name, $this->_parameters)) {
            unset($this->_parameters[$name]);
        }
    }

    /**
     * Return an array of optional parameters for (un)subscribe requests
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->_parameters;
    }

    /**
     * Subscribe to one or more Hub Servers using the stored Hub URLs
     * for the given Topic URL (RSS or Atom feed)
     *
     */
    public function subscribe()
    {
        $client = $this->_getHttpClient('subscribe');
    }

    /**
     * Unsubscribe from one or more Hub Servers using the stored Hub URLs
     * for the given Topic URL (RSS or Atom feed)
     *
     */
    public function unsubscribe()
    {
        $client = $this->_getHttpClient('unsubscribe');
    }

    /**
     * This should be called when a request is received at this Subscriber's
     * configured Callback URL, which is used by all Hub Servers to pass
     * responding or notification requests to.
     *
     */
    public function handleCallback()
    {

    }

    /**
     * Get a basic prepared HTTP client for use
     *
     * @param string $mode Must be "subscribe" or "unsubscribe"
     * @return Zend_Http_Client
     */
    protected function _getHttpClient($mode)
    {
        if (!in_array($mode, array('subscribe', 'unsubscribe'))) {
            require_once 'Zend/Pubsubhubbub/Exception.php';
            throw new Zend_Pubsubhubbub_Exception('Invalid mode specified: '
            . $mode . ' which should have been "subscribe" or "unsubscribe"');
        }
        $client = Zend_Pubsubhubbub::getHttpClient();
        $client->setMethod(Zend_Http_Client::POST);
        $client->setConfig(array('useragent' => 'Zend_Pubsubhubbub_Subscriber/'
            . Zend_Version::VERSION));
        $params = array();
        $params[] = 'hub.mode=' . $mode;
        $optParams = $this->getParameters();
        foreach ($optParams as $name => $value) {
            $params[] = urlencode($name) . '=' . urlencode($value);
        }
        $paramString = implode('&', $params);
        $client->setRawData($paramString);
        return $client;
    }

}
