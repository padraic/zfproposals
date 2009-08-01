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
 * NOTE: For simplicity when implementing, this class WILL send all parameters
 * noted in the specification as OPTIONAL. Opt-outs will be added to
 * configuration possibilities in the near future, or this behaviour will
 * reverse if they are absolutely unnecessary.
 */

/**
 * NOTE: Specification refers to verify_token as "opaque token". This has been
 * interpreted as a token whose content is not readily apparent. A precise
 * example/definition will be requested on the mailing lists. For now, a simple
 * text string is utilised generated from uniqid(true) (similar to how a nonce
 * would be generated in practice).
 */

/**
 * NOTE: For future reference, the current implementation is considered to
 * be in a PLAINTEXT style (a la OAuth) where no digital signing of
 * requests is assumed. Signing will need some changes, specifically
 * parameters need to follow a signature base string, where parameters are
 * ordered by name to ensure all parties can replicate the generated
 * signature despite any possible disordering of parameters to be signed.
 * INFO: Check reference implementation for how handled...
 * INFO: Where's the nonce to prevent replays of past signed requests?
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
     * The preferred verification mode (sync or async). By default, this
     * Subscriber prefers synchronous verification, but does support
     * asynchronous if that's the Hub Server's utilised mode.
     *
     * Zend_Pubsubhubbub_Subscriber will always send both modes, whose
     * order of occurance in the parameter list determines this preference.
     *
     * @var string
     */
    protected $_preferredVerificationMode
        = Zend_Pubsubhubbub::VERIFICATION_MODE_SYNC;

    /**
     * The verification tokens to accompany any (un)subscribe requests
     * sent to the Hub Servers. This acts to identify the request, and
     * subsequent Hub Server response, so they should be a unique value
     * retained for matching to a later response from a Hub Server.
     *
     * Zend_Pubsubhubbub_Subscriber will generate these if none is given and you
     * MUST retain these until the (un)subscribe request has been verified and
     * confirmed by the Hub Servers.
     *
     * @var string
     */
    protected $_verificationTokens = array();

    /**
     * An array of any errors including keys for 'response', 'hubUrl'.
     * The response is the actual Zend_Http_Response object.
     *
     * @var array
     */
    protected $_errors = array();

    /**
     * An array of Hub Server URLs for Hubs operating at this time in
     * asynchronous verification mode.
     *
     * @var array
     */
    protected $_asyncHubs = array();

    /**
     * An instance of Zend_Pubsubhubbub_StorageInterface used to background
     * save any verification tokens associated with a subscription or other.
     *
     * @var Zend_Pubsubhubbub_StorageInterface
     */
    protected $_storage = null;

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
        if (array_key_exists('preferredVerificationMode', $config)) {
            $this->setPreferredVerificationMode(
                $config['preferredVerificationMode']
            );
        }
        if (array_key_exists('verificationTokens', $config)) {
            $this->setVerificationTokens($config['verificationTokens']);
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
     * Set preferred verification mode (sync or async). By default, this
     * Subscriber prefers synchronous verification, but does support
     * asynchronous if that's the Hub Server's utilised mode.
     *
     * Zend_Pubsubhubbub_Subscriber will always send both modes, whose
     * order of occurance in the parameter list determines this preference.
     *
     * @param string $mode Should be 'sync' or 'async'
     */
    public function setPreferredVerificationMode($mode)
    {
        if ($mode !== Zend_Pubsubhubbub::VERIFICATION_MODE_SYNC
        && $mode !== Zend_Pubsubhubbub::VERIFICATION_MODE_ASYNC) {
            require_once 'Zend/Pubsubhubbub/Exception.php';
            throw new Zend_Pubsubhubbub_Exception('Invalid preferred'
            . ' mode specified: "' . $mode . '" but should be one of'
            . ' Zend_Pubsubhubbub::VERIFICATION_MODE_SYNC or'
            . ' Zend_Pubsubhubbub::VERIFICATION_MODE_ASYNC');
        }
        $this->_preferredVerificationMode = $mode;
    }

    /**
     * Get preferred verification mode (sync or async).
     *
     * @return string
     */
    public function getPreferredVerificationMode()
    {
        return $this->_preferredVerificationMode;
    }

    /**
     * The verification token to accompany any (un)subscribe requests
     * sent to a Hub Server. This acts to identify the request, and
     * subsequent Hub Server response, so it should be a unique value
     * retained for matching to a later response from the Hub Server.
     *
     * Zend_Pubsubhubbub_Subscriber will generate this if none is given and you
     * MUST retain this until the (un)subscribe request has been verified and
     * confirmed by the Hub Server.
     *
     * @param string $url The Hub Server URL this token applies to
     * @param string $token
     */
    public function setVerificationToken($url, $token)
    {
        if (empty($url) || !is_string($url) || !Zend_Uri::check($url)) {
            require_once 'Zend/Pubsubhubbub/Exception.php';
            throw new Zend_Pubsubhubbub_Exception('Invalid parameter "url"'
                .' of "' . $url . '" must be a non-empty string and a valid'
                .'URL');
        }
        if (empty($token) || !is_string($token)) {
            require_once 'Zend/Pubsubhubbub/Exception.php';
            throw new Zend_Pubsubhubbub_Exception('Invalid verification token'
            . ': "' . $token . '" must be a non-empty string');
        }
        $this->_verificationTokens[$url] = $token;
    }

    /**
     * Set an array of verification tokens by Hub Server URL
     *
     * @param array $tokens Assoc array indexed by Hub Server URL
     */
    public function setVerificationTokens(array $tokens)
    {
        foreach ($tokens as $hubUrl => $token) {
            $this->setVerificationToken($hubUrl, $token);
        }
    }

    /**
     * Get/Generate a verification token.
     *
     * @param string $hubUrl The Hub Server whose token will be returned
     * @return string
     */
    public function getVerificationToken($hubUrl)
    {
        if (!isset($this->_verificationTokens[$hubUrl])
        || empty($this->_verificationTokens[$hubUrl])) {
            if (!in_array($hubUrl, $this->getHubUrls())) {
                require_once 'Zend/Pubsubhubbub/Exception.php';
                throw new Zend_Pubsubhubbub_Exception('Unable to return a'
                . ' verification token as the given Hub Server URL "'
                . $hubUrl . '" is not known');
            }
            $this->_verificationToken = $this->_generateToken($hubUrl);
        }
        return $this->_verificationTokens[$hubUrl];
    }

    /**
     * Get an array of verification tokens by Hub Server URL
     *
     * @return array $tokens Assoc array indexed by Hub Server URL
     */
    public function getVerificationTokens()
    {
        return $this->_verificationTokens;
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

    public function subscribeToTopic($url)
    {
        if (empty($url) || !is_string($url) || !Zend_Uri::check($url)) {
            require_once 'Zend/Pubsubhubbub/Exception.php';
            throw new Zend_Pubsubhubbub_Exception('Invalid parameter "url"'
                .' of "' . $url . '" must be a non-empty string and a valid'
                .'URL');
        }
        $client = $this->_getHttpClient();
        $client->setUri($url);
        $response = $client->request();
        if ($response->getStatus() !== '204') {
            require_once 'Zend/Pubsubhubbub/Exception.php';
            throw new Zend_Pubsubhubbub_Exception('Notification to Hub Server '
            . 'at "' . $url . '" appears to have failed with a status code of "'
            . $response->getStatus() . '" and message "'
            . $response->getMessage() . '"');
        }
    }

    public function unsubscribeFromTopic($url)
    {
        if (empty($url) || !is_string($url) || !Zend_Uri::check($url)) {
            require_once 'Zend/Pubsubhubbub/Exception.php';
            throw new Zend_Pubsubhubbub_Exception('Invalid parameter "url"'
                .' of "' . $url . '" must be a non-empty string and a valid'
                .'URL');
        }
        $client = $this->_getHttpClient();
        $client->setUri($url);
        $response = $client->request();
        if ($response->getStatus() !== '204') {
            require_once 'Zend/Pubsubhubbub/Exception.php';
            throw new Zend_Pubsubhubbub_Exception('Notification to Hub Server '
            . 'at "' . $url . '" appears to have failed with a status code of "'
            . $response->getStatus() . '" and message "'
            . $response->getMessage() . '"');
        }
    }

    /**
     * TODO: Add same as last two methods only Hub (not Topic) specific
     */

    /**
     * Subscribe to one or more Hub Servers using the stored Hub URLs
     * for the given Topic URL (RSS or Atom feed)
     *
     */
    public function subscribeAll()
    {
        $client = $this->_getHttpClient();
        $hubs = $this->getHubUrls();
        if (empty($hubs)) {
            require_once 'Zend/Pubsubhubbub/Exception.php';
            throw new Zend_Pubsubhubbub_Exception('No Hub Server URLs'
            . ' have been set so no subscriptions can be attempted');
        }
        $this->_errors = array();
        $this->_asyncHubs = array();
        foreach ($hubs as $url) {
            $client->setUri($url);
            $client->setRawData($this->_getRequestParameters($url, 'subscribe'));
            $response = $client->request();
            if ($response->getStatus() !== '204'
            && $response->getStatus() !== '202') {
                $this->_errors[] = array(
                    'response' => $response,
                    'hubUrl' => $url
                );
            /**
             * At first I thought it was needed, but the backend storage will
             * allow tracking async without any user interference. It's left
             * here in case the user is interested in knowing what Hubs
             * are using async verification modes
             */
            } elseif ($response->getStatus() == '202') {
                $this->_asyncHubs[] = array(
                    'response' => $response,
                    'hubUrl' => $url
                );
            }
        }
    }

    /**
     * Unsubscribe from one or more Hub Servers using the stored Hub URLs
     * for the given Topic URL (RSS or Atom feed)
     *
     */
    public function unsubscribeAll()
    {
        $client = $this->_getHttpClient();
        $hubs = $this->getHubUrls();
        if (empty($hubs)) {
            require_once 'Zend/Pubsubhubbub/Exception.php';
            throw new Zend_Pubsubhubbub_Exception('No Hub Server URLs'
            . ' have been set so no subscriptions can be attempted');
        }
        $this->_errors = array();
        $this->_asyncHubs = array();
        foreach ($hubs as $url) {
            $client->setUri($url);
            $client->setRawData($this->_getRequestParameters($url, 'unsubscribe'));
            $response = $client->request();
            if ($response->getStatus() !== '204'
            && $response->getStatus() !== '202') {
                $this->_errors[] = array(
                    'response' => $response,
                    'hubUrl' => $url
                );
            /**
             * At first I thought it was needed, but the backend storage will
             * allow tracking async without any user interference. It's left
             * here in case the user is interested in knowing what Hubs
             * are using async verification modes
             */
            } elseif ($response->getStatus() == '202') {
                $this->_asyncHubs[] = array(
                    'response' => $response,
                    'hubUrl' => $url
                );
            }
        }
    }

    /**
     * This should be called when a request is received at this Subscriber's
     * configured Callback URL, which is used by all Hub Servers to pass
     * responding or notification requests to.
     *
     */
    public function handleCallback() // MOVE TO SEPARATE CLASS?
    {

    }

    /**
     * Returns a boolean indicator of whether the notifications to Hub
     * Servers were ALL successful. If even one failed, FALSE is returned.
     *
     * @return bool
     */
    public function isSuccess()
    {
        if (count($this->_errors) > 0) {
            return false;
        }
        return true;
    }

    /**
     * Return an array of errors met from any failures, including keys:
     * 'response' => the Zend_Http_Response object from the failure
     * 'hubUrl' => the URL of the Hub Server whose notification failed
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * Return an array of Hub Server URLs who returned a response indicating
     * operation in Asynchronous Verification Mode, i.e. they will not confirm
     * any (un)subscription immediately but at a later time (Hubs may be
     * doing this as a batch process when load balancing)
     *
     * @return array
     */
    public function getAsyncHubs()
    {
        return $this->_asyncHubs;
    }

    /**
     * Get a basic prepared HTTP client for use
     *
     * @param string $mode Must be "subscribe" or "unsubscribe"
     * @return Zend_Http_Client
     */
    protected function _getHttpClient()
    {
        $client = Zend_Pubsubhubbub::getHttpClient();
        $client->setMethod(Zend_Http_Client::POST);
        $client->setConfig(array('useragent' => 'Zend_Pubsubhubbub_Subscriber/'
            . Zend_Version::VERSION));
        return $client;
    }

    /**
     * Return a list of standard protocol/optional parameters for addition to
     * client's POST body that are specific to the current Hub Server URL
     *
     * @param string $hubUrl
     */
    protected function _getRequestParameters($hubUrl, $mode)
    {
        if (!in_array($mode, array('subscribe', 'unsubscribe'))) {
            require_once 'Zend/Pubsubhubbub/Exception.php';
            throw new Zend_Pubsubhubbub_Exception('Invalid mode specified: "'
            . $mode . '" which should have been "subscribe" or "unsubscribe"');
        }
        $params = array();
        $params[] = array('hub.mode', $mode);
        $params[] = array('hub.callback', $this->getCallbackUrl());
        $params[] = array('hub.topic', $this->getTopicUrl());
        if ($this->getPreferredVerificationMode()
        == Zend_Pubsubhubbub::VERIFICATION_MODE_SYNC) {
            $vmodes = array(Zend_Pubsubhubbub::VERIFICATION_MODE_SYNC,
            Zend_Pubsubhubbub::VERIFICATION_MODE_ASYNC);
        } else {
            $vmodes = array(Zend_Pubsubhubbub::VERIFICATION_MODE_ASYNC,
            Zend_Pubsubhubbub::VERIFICATION_MODE_SYNC);
        }
        foreach($vmodes as $vmode) {
            $params[] = array('hub.verify', $vmode);
        }
        $params[] = array('hub.verify_token', $this->getVerificationToken($hubUrl));
        if ($mode == 'subscribe') {
            $params[] = array('hub.lease_seconds', $this->getLeaseSeconds());
        }
        $optParams = $this->getParameters();
        foreach ($optParams as $name => $value) {
            $params[] = array($name, $value);
        }
        $paramsEncoded = array();
        foreach ($params as $param) {
            $paramsEncoded[] = urlencode($param[0]) . '=' . urlencode($param[1]);
        }
        return implode('&', $paramsEncoded);
    }

    /**
     * Simple helper to generate a verification token used in (un)subscribe
     * requests to a Hub Server
     *
     * @param string $hubUrl The Hub Server URL for which this token will apply
     * @return string
     */
    protected function _generateToken($hubUrl)
    {

    }

}
