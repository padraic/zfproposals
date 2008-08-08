<?php
session_start();

require_once 'Zend/Oauth/Consumer.php';
require_once 'Zend/Crypt/Rsa/Key/Private.php';

$options = array(
    'requestScheme' => Zend_Oauth::REQUEST_SCHEME_HEADER, // Google does not support POSTBODY option
    'version' => '1.0',
    'signatureMethod' => 'RSA-SHA1',
    'localUrl' => 'http://path/to/this/file.php', // UPDATE FOR YOUR DETAILS
    'requestTokenUrl' => 'https://www.google.com/accounts/OAuthGetRequestToken',
    'userAuthorisationUrl' => 'https://www.google.com/accounts/OAuthAuthorizeToken',
    'accessTokenUrl' => 'https://www.google.com/accounts/OAuthGetAccessToken',
    'consumerKey' => 'exmaple.com',  // UPDATE FOR YOUR DETAILS
    'consumerSecret' => new Zend_Crypt_Rsa_Key_Private(
        file_get_contents(realpath('./myrsakey.pem'))  // UPDATE FOR YOUR DETAILS
    )
);

/**
 * Example utilises the Google Contacts API
 */

$consumer = new Zend_Oauth_Consumer($options);

if (!isset($_SESSION['ACCESS_TOKEN_GOOGLE'])) {
    if (!empty($_GET)) {
        $token = $consumer->getAccessToken($_GET, unserialize($_SESSION['REQUEST_TOKEN_GOOGLE']), Zend_Oauth::GET);
        $_SESSION['ACCESS_TOKEN_GOOGLE'] = serialize($token);
    } else {
        $token = $consumer->getRequestToken(array('scope'=>'http://www.google.com/m8/feeds'), Zend_Oauth::GET);
        $_SESSION['REQUEST_TOKEN_GOOGLE'] = serialize($token);
        $consumer->redirect();
        exit;
    }
} else {
    $token = unserialize($_SESSION['ACCESS_TOKEN_GOOGLE']);
    $_SESSION['ACCESS_TOKEN_GOOGLE'] = null;
}

// OAuth Access Token Retreived; Proceed to query Data API for current user's contacts
$client = $token->getHttpClient($options);
$client->setUri('http://www.google.com/m8/feeds/groups/default/full');
$client->setMethod(Zend_Http_Client::GET);

$response = $client->request();
header('Content-Type: ' . $response->getHeader('Content-Type'));
echo $response->getBody();