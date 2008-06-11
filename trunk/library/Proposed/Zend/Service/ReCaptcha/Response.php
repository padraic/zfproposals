<?php
class Zend_Service_ReCaptcha_Response
{
    /**
     * Status
     *
     * true if the response is valid or false otherwise
     *
     * @var boolean
     */
    protected $_status = null;

    /**
     * Error code
     *
     * The error code if the status is false. The different error codes can be found in the
     * recaptcha API docs.
     *
     * @var string
     */
    protected $_errorCode = null;

    /**
     * Class constructor used to construct a response
     *
     * @param string $status
     * @param string $errorCode
     * @param Zend_Http_Response $httpResponse If this is set the content will override $status and $errorCode
     */
    public function __construct($status = null, $errorCode = null, Zend_Http_Response $httpResponse = null)
    {
        if ($status !== null) {
            $this->setStatus($status);
        }

        if ($errorCode !== null) {
            $this->setErrorCode($errorCode);
        }

        if ($httpResponse !== null) {
            $this->setFromHttpResponse($httpResponse);
        }
    }

    /**
     * Set the status
     *
     * @param string $status
     * @return Zend_Service_ReCaptcha_Response
     */
    public function setStatus($status)
    {
        if ($status === 'true') {
            $this->_status = true;
        } else {
            $this->_status = false;
        }

        return $this;
    }

    /**
     * Get the status
     *
     * @return boolean
     */
    public function getStatus()
    {
        return $this->_status;
    }

    /**
     * Alias for getStatus()
     *
     * @return boolean
     */
    public function isValid()
    {
        return $this->getStatus();
    }

    /**
     * Set the error code
     *
     * @param string $errorCode
     * @return Zend_Service_ReCaptcha_Response
     */
    public function setErrorCode($errorCode)
    {
        $this->_errorCode = $errorCode;

        return $this;
    }

    /**
     * Get the error code
     *
     * @return string
     */
    public function getErrorCode()
    {
        return $this->_errorCode;
    }

    /**
     * Populate this instance based on a Zend_Http_Response object
     *
     * @param Zend_Http_Response $response
     * @return Zend_Service_ReCaptcha_Response
     */
    public function setFromHttpResponse(Zend_Http_Response $response)
    {
        $body = $response->getBody();

        list($status, $errorCode) = explode("\n", $body, 2);

        $this->setStatus($status);
        $this->setErrorCode($errorCode);

        return $this;
    }
}