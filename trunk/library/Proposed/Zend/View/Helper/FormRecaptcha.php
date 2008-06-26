<?php

require_once 'Zend/Service/Recaptcha.php';

require_once 'Zend/View/Helper/FormElement.php';

class Zend_View_Helper_FormRecaptcha extends Zend_View_Helper_FormElement
{

    protected static $httpClient = null;

    public static function setHttpClient(Zend_Http_Client $httpClient)
    {
        self::$httpClient = $httpClient;
    }

    public function formRecaptcha($name, $value = null, array $attribs = null)
    {
        $info = $this->_getInfo($name, $value, $attribs);
        extract($info);

        $params = null;
        if (isset($attribs['recaptchaParams'])) {
            $params = $attribs['recaptchaParams'];
            unset($attribs['recaptchaParams']);
        } else {
            throw new Zend_View_Exception('Recaptcha "recaptchaParams" array missing from attributes');
        }

        $recaptchaOptions = null;
        if (isset($attribs['recaptchaOptions'])) {
            $recaptchaOptions = $attribs['recaptchaOptions'];
            unset($attribs['recaptchaOptions']);
        }

        if (!isset($params['privateKey']) || !isset($params['publicKey'])
            || empty($params['privateKey']) || empty($params['publicKey']))
        {
            throw new Zend_View_Exception('Public and private keys must be set');
        }
        $privateKey = $params['privateKey'];
        $publicKey = $params['publicKey'];
        unset($params['privateKey'], $params['publicKey']);
        $recaptcha = new Zend_Service_Recaptcha($publicKey, $privateKey);
        if (!is_null(self::$httpClient)) {
            self::$httpClient->resetParameters();
            $recaptcha->setHttpClient(self::$httpClient);
        }
        if (!empty($params)) {
            $recaptcha->setParams($params);
        }
        if (!is_null($recaptchaOptions)) {
            $recaptcha->setOptions($recaptchaOptions);
        }
        return (string) $recaptcha;
    }
}