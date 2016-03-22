<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Mail\TestAsset;

use Zend\Mail\Protocol\Smtp;
use Zend\Mail\Protocol\Exception;

/**
 * Test spy to use when testing SMTP protocol
 */
class SmtpProtocolSpy extends Smtp
{
    public $calledQuit = false;
    protected $connect = false;
    protected $mail;
    protected $rcptTest = array();
    protected $lastSendRequest;
    protected $raiseExpectExceptonOnNextQuit = false;

    public function connect()
    {
        $this->connect = true;
        $this->_startTime();

        return true;
    }

    public function disconnect()
    {
        $this->connect = false;
        parent::disconnect();
    }

    public function quit($completeQuit = true)
    {
        $this->calledQuit = true;
        parent::quit($completeQuit);

        $this->raiseExpectExceptonOnNextQuit = false;
    }

    public function rset()
    {
        parent::rset();
        $this->rcptTest = array();
    }

    public function rcpt($to)
    {
        parent::rcpt($to);
        $this->rcpt = true;
        $this->rcptTest[] = $to;
    }

    protected function _send($request)
    {
        $this->lastSendRequest = $request;

        // Save request to internal log
        $this->_addLog($request . self::EOL);
    }

    protected function _expect($code, $timeout = null)
    {
        if ($this->raiseExpectExceptonOnNextQuit && strpos($this->lastSendRequest, 'QUIT') === 0) {
            throw new Exception\RuntimeException($this->lastSendRequest);
        }

        return '';
    }

    /**
     * Are we connected?
     *
     * @return bool
     */
    public function isConnected()
    {
        return $this->connect;
    }

    /**
     * Get value of mail property
     *
     * @return null|string
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * Get recipients
     *
     * @return array
     */
    public function getRecipients()
    {
        return $this->rcptTest;
    }

    /**
     * Get Auth Status
     *
     * @return bool
     */
    public function getAuth()
    {
        return $this->auth;
    }

    /**
     * Set Auth Status
     *
     * @param  bool $status
     * @return self
     */
    public function setAuth($status)
    {
        $this->auth = (bool) $status;

        return $this;
    }

    /**
     * Get Session Status
     *
     * @return bool
     */
    public function getSessionStatus()
    {
        return $this->sess;
    }

    /**
     * Set Session Status
     *
     * @param  bool $status
     * @return self
     */
    public function setSessionStatus($status)
    {
        $this->sess = (bool) $status;

        return $this;
    }

    /**
     * Set Session Status
     *
     * @param  bool $status
     * @return self
     */
    public function setRaiseExpectExceptonOnNextQuit($raiseExpectExceptonOnNextQuit)
    {
        $this->raiseExpectExceptonOnNextQuit = (bool) $raiseExpectExceptonOnNextQuit;

        return $this;
    }
}
