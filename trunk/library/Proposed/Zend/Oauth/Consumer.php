<?php

/**
 * For possible future refactoring to Server
 */
require_once 'Zend/Oauth.php';

class Zend_Oauth_Consumer extends Zend_Oauth
{
    
    /**
     * Signature method utilised by OAuth consumer; defaults to HMAC-SHA1 and
     * the only other supported option (for now) is RSA-SHA1.
     *
     * @var string
     */
    protected $_signatureMethod = 'HMAC-SHA1';



}