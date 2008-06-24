<?php

interface Zend_Oauth_Config_Interface
{

    public function setOptions(array $options);

    public function setConsumerKey($key);

    public function getConsumerKey();

    public function setConsumerSecret($secret);

    public function getConsumerSecret();

    public function setSignatureMethod($method);

    public function getSignatureMethod();

    public function setRequestScheme($scheme);

    public function getRequestScheme();

    public function getRequestMethod();

    public function setVersion($version);

    public function getVersion();

    public function setLocalUrl($url);

    public function getLocalUrl();

    public function setRequestTokenUrl($url);

    public function getRequestTokenUrl();

    public function setAccessTokenUrl($url);

    public function getAccessTokenUrl();

    public function setUserAuthorisationUrl($url);

    public function getUserAuthorisationUrl();

}