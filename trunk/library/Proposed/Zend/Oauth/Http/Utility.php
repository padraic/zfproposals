<?php

require_once 'Zend/Oauth.php';

require_once 'Zend/Oauth/Http.php';

require_once 'Zend/Oauth/Signature/Hmac.php';

require_once 'Zend/Oauth/Signature/Rsa.php';

require_once 'Zend/Oauth/Signature/Plaintext.php';

class Zend_Oauth_Http_Utility
{

    public function assembleParams($url, Zend_Oauth_Config_Interface $config, array $serviceProviderParams = null)
    {
        $params = array();
        $params['oauth_consumer_key'] = $config->getConsumerKey();
        $params['oauth_nonce'] = $this->generateNonce();
        $params['oauth_signature_method'] = $config->getSignatureMethod();
        $params['oauth_timestamp'] = $this->generateTimestamp();
        $params['oauth_token'] = $config->getToken()->getToken();
        $params['oauth_version'] = $config->getVersion();
        if (!is_null($serviceProviderParams)) {
            $params = array_merge($params, $serviceProviderParams);
        }
        $params['oauth_signature'] = $this->sign(
            $params,
            $config->getSignatureMethod(),
            $config->getConsumerSecret(),
            $config->getToken()->getTokenSecret(),
            $config->getRequestMethod(),
            $url
        );
        return $params;
    }

    public function toEncodedQueryString(array $params, $customParamsOnly = false)
    {
        if ($customParamsOnly) {
            foreach ($params as $key=>$value) {
                if (preg_match("/^oauth_/", $key)) {
                    unset($params[$key]);
                }
            }
        }
        $encodedParams = array();
        foreach ($params as $key => $value) {
            $encodedParams[] =
                self::urlEncode($key) . '=' . self::urlEncode($value);
        }
        return implode('&', $encodedParams);
    }

    public function toAuthorizationHeader(array $params, $realm = null, $excludeCustomParams = true)
    {
        $headerValue = array();
        $headerValue[] = 'OAuth realm="' . $realm . '"';
        foreach ($params as $key => $value) {
            if ($excludeCustomParams) {
                if (!preg_match("/^oauth_/", $key)) {
                    continue;
                }
            }
            $headerValue[] =
                self::urlEncode($key) . '="'
                . self::urlEncode($value) . '"';
        }
        return implode(",", $headerValue);
    }

    public function sign(array $params, $signatureMethod, $consumerSecret, $tokenSecret = null, $method = null, $url = null)
    {
        $className = '';
        $hashAlgo = null;
        $parts = explode('-', $signatureMethod);
        if (count($parts) > 1) {
            $className = 'Zend_Oauth_Signature_' . ucfirst(strtolower($parts[0]));
            $hashAlgo = $parts[1];
        } else {
            $className = 'Zend_Oauth_Signature_' . ucfirst(strtolower($signatureMethod));
        }
        $signatureObject = new $className($consumerSecret, $tokenSecret, $hashAlgo);
        return $signatureObject->sign($params, $method, $url);
    }

    public function parseQueryString($query)
    {
        $params = array();
        if (empty($query)) {
            return array();
        }
        // not remotely perfect but beats parse_str() which converts
        // periods and uses urldecode, not rawurldecode.
        $parts = explode('&', $query);
        foreach ($parts as $pair) {
            $kv = explode('=', $pair);
            $params[rawurldecode($kv[0])] = rawurldecode($kv[1]);
        }
        return $params;
    }

    public function generateNonce()
    {
        return md5(uniqid(rand(), true));
    }

    public function generateTimestamp()
    {
        return time();
    }

    public static function urlEncode($value)
    {
        $encoded = rawurlencode($value);
        return str_replace('%7E','~',$encoded);
    }
}