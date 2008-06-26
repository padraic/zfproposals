<?php
session_start();

require_once 'Zend/Oauth/Consumer.php';
require_once 'Zend/Crypt/Rsa/Key/Private.php';

$options = array(
    'requestScheme' => Zend_Oauth::REQUEST_SCHEME_HEADER,
    'version' => '1.0',
    'signatureMethod' => 'RSA-SHA1',
    'localUrl' => 'path/to/this/file.php',
    'requestTokenUrl' => 'https://www.google.com/accounts/OAuthGetRequestToken?scope=http://www.google.com/m8/feeds',
    'userAuthorisationUrl' => 'https://www.google.com/accounts/OAuthAuthorizeToken',
    'accessTokenUrl' => 'https://www.google.com/accounts/OAuthGetAccessToken',
    'consumerKey' => 'dev.phpspec.org',
    'consumerSecret' => new Zend_Crypt_Rsa_Key_Private(
        file_get_contents(realpath('./myrsakey.pem'));
    )
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
    $_SESSION['ACCESS_TOKEN'] = null;
}

$client = $token->getHttpClient($options);
$client->setUri('http://');
$client->setMethod(Zend_Http_Client::POST);
//$client->setParameterPost();

$response = $client->request();
header('Content-Type: ' . $response->getHeader('Content-Type'));
echo $response->getBody();