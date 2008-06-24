<?php
session_start();

require_once 'Zend/Oauth/Consumer.php';

$options = array(
    'requestScheme' => Zend_Oauth::REQUEST_SCHEME_HEADER,
    'version' => '1.0',
    'signatureMethod' => 'HMAC-SHA1',
    'localUrl' => 'http://your/path/to/this/file.php',
    'requestTokenUrl' => 'http://ma.gnolia.com/oauth/get_request_token',
    'userAuthorisationUrl' => 'http://ma.gnolia.com/oauth/authorize',
    'accessTokenUrl' => 'http://ma.gnolia.com/oauth/get_access_token',
    'consumerKey' => 'YOUR_CONSUMER_KEY',
    'consumerSecret' => 'YOUR_CONSUMER_KEY_SECRET'
);

$consumer = new Zend_Oauth_Consumer($options);

if (!isset($_SESSION['ACCESS_TOKEN'])) {
    if (!empty($_GET)) {
        $token = $consumer->getAccessToken($_GET, unserialize($_SESSION['REQUEST_TOKEN']));
        $_SESSION['ACCESS_TOKEN'] = serialize($token);
    } else {
        $token = $consumer->getRequestToken();
        $_SESSION['REQUEST_TOKEN'] = serialize($token);
        $consumer->redirect();
    }
} else {
    $token = unserialize($_SESSION['ACCESS_TOKEN']);
    $_SESSION['ACCESS_TOKEN'] = null; // forces reset of access token on all example runs ;)
}

$client = $token->getHttpClient($options);
$client->setUri('http://ma.gnolia.com/api/rest/2/bookmarks_count');
$client->setMethod(Zend_Http_Client::POST);
$client->setParameterPost('group','oauth');

$response = $client->request();
header('Content-Type: ' . $response->getHeader('Content-Type'));
echo $response->getBody();