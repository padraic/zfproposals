<?php

require_once 'Zend/Service/Recaptcha.php';

require_once 'Zend/Validate/Abstract.php';

class Zend_Validate_Recaptcha extends Zend_Validate_Abstract
{

    const MISSING_CHALLENGE_FIELD = 'MISSING_CHALLENGE_FIELD';
    const INVALID_SITE_PUBLIC_KEY = 'INVALID_SITE_PUBLIC_KEY';
    const INVALID_SITE_PRIVATE_KEY = 'INVALID_SITE_PRIVATE_KEY';
    const INVALID_REQUEST_COOKIE = 'INVALID_REQUEST_COOKIE';
    const INCORRECT_CAPTCHA_SOL = 'INCORRECT_CAPTCHA_SOL';
    const VERIFY_PARAMS_INCORRECT = 'VERIFY_PARAMS_INCORRECT';
    const INVALID_REFERRER = 'INVALID_REFERRER';
    const RECAPTCHA_NOT_REACHABLE = 'RECAPTCHA_NOT_REACHABLE';

    protected $_messageTemplates = array(
        self::MISSING_CHALLENGE_FIELD => "Challenge field is missing",
        self::INVALID_SITE_PUBLIC_KEY => "Unable to validate the public key",
        self::INVALID_SITE_PRIVATE_KEY => "Unable to validate the private key",
        self::INVALID_REQUEST_COOKIE => "The challenge parameter of the verify script is incorrect",
        self::INCORRECT_CAPTCHA_SOL => "The CAPTCHA solution is incorrect",
        self::VERIFY_PARAMS_INCORRECT => "The parameters to verify are incorrect",
        self::INVALID_REFERRER => "reCAPTCHA keys invalid for this domain",
        self::RECAPTCHA_NOT_REACHABLE => "Unable to contact reCAPTCHA service",
    );

    protected static $httpClient = null;

    protected $publicKey = null;

    protected $privateKey = null;

    public static function setHttpClient(Zend_Http_Client $httpClient)
    {
        self::$httpClient = $httpClient;
    }

    public function __construct($publicKey, $privateKey)
    {
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
    }

    public function isValid($value)
    {
        // Grab non-prototyped $context param set from Zend_Form
        $params = func_get_args();
        $context = null;
        if (isset($params[1])) {
            $context = $params[1];
        }
        if (is_null($context) || !isset($context['recaptcha_challenge_field'])) {
            $this->_error(self::MISSING_CHALLENGE_FIELD);
            return false;
        }
        $recaptcha = new Zend_Service_Recaptcha(
            $this->publicKey, $this->privateKey
        );
        if (!is_null(self::$httpClient)) {
            self::$httpClient->resetParameters();
            $recaptcha->setHttpClient(self::$httpClient);
        }
        $result = $recaptcha->verify($context['recaptcha_challenge_field'], $value);
        if (!$result->isValid()) {
            $errorCode = strtoupper(
                str_replace("-", '_', $result->getErrorCode())
            );
            $this->_error($errorCode);
            return false;
        }
        return true;
    }
}