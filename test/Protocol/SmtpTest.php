<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Mail\Protocol;

use Zend\Mail\Headers;
use Zend\Mail\Message;
use Zend\Mail\Transport\Smtp;
use ZendTest\Mail\TestAsset\SmtpProtocolSpy;

/**
 * @group      Zend_Mail
 */
class SmtpTest extends \PHPUnit_Framework_TestCase
{
    /** @var Smtp */
    public $transport;
    /** @var SmtpProtocolSpy */
    public $connection;

    public function setUp()
    {
        $this->transport  = new Smtp();
        $this->connection = new SmtpProtocolSpy();
        $this->transport->setConnection($this->connection);
    }

    public function testSendMinimalMail()
    {
        $headers = new Headers();
        $headers->addHeaderLine('Date', 'Sun, 10 Jun 2012 20:07:24 +0200');
        $message = new Message();
        $message
            ->setHeaders($headers)
            ->setSender('ralph.schindler@zend.com', 'Ralph Schindler')
            ->setBody('testSendMailWithoutMinimalHeaders')
            ->addTo('zf-devteam@zend.com', 'ZF DevTeam')
        ;
        $expectedMessage = "EHLO localhost\r\n"
                           . "MAIL FROM:<ralph.schindler@zend.com>\r\n"
                           . "RCPT TO:<zf-devteam@zend.com>\r\n"
                           . "DATA\r\n"
                           . "Date: Sun, 10 Jun 2012 20:07:24 +0200\r\n"
                           . "Sender: Ralph Schindler <ralph.schindler@zend.com>\r\n"
                           . "To: ZF DevTeam <zf-devteam@zend.com>\r\n"
                           . "\r\n"
                           . "testSendMailWithoutMinimalHeaders\r\n"
                           . ".\r\n";

        $this->transport->send($message);

        $this->assertEquals($expectedMessage, $this->connection->getLog());
    }

    public function testSendEscapedEmail()
    {
        $headers = new Headers();
        $headers->addHeaderLine('Date', 'Sun, 10 Jun 2012 20:07:24 +0200');
        $message = new Message();
        $message
            ->setHeaders($headers)
            ->setSender('ralph.schindler@zend.com', 'Ralph Schindler')
            ->setBody("This is a test\n.")
            ->addTo('zf-devteam@zend.com', 'ZF DevTeam')
        ;
        $expectedMessage = "EHLO localhost\r\n"
            . "MAIL FROM:<ralph.schindler@zend.com>\r\n"
            . "RCPT TO:<zf-devteam@zend.com>\r\n"
            . "DATA\r\n"
            . "Date: Sun, 10 Jun 2012 20:07:24 +0200\r\n"
            . "Sender: Ralph Schindler <ralph.schindler@zend.com>\r\n"
            . "To: ZF DevTeam <zf-devteam@zend.com>\r\n"
            . "\r\n"
            . "This is a test\r\n"
            . "..\r\n"
            . ".\r\n";

        $this->transport->send($message);

        $this->assertEquals($expectedMessage, $this->connection->getLog());
    }

    public function testDisconnectCallsQuit()
    {
        $this->connection->disconnect();
        $this->assertTrue($this->connection->calledQuit);
    }

    public function testDisconnectResetsAuthFlag()
    {
        $this->connection->connect();
        $this->connection->setSessionStatus(true);
        $this->connection->setAuth(true);
        $this->assertTrue($this->connection->getAuth());
        $this->connection->disconnect();
        $this->assertFalse($this->connection->getAuth());
    }

    public function testStartTime()
    {
        $yesterday = (time() - 86400);
        $this->assertNull($this->connection->getStartTime());
        $this->connection->connect();
        $this->assertGreaterThan($yesterday, $this->connection->getStartTime());
        $this->connection->disconnect();
        $this->assertNull($this->connection->getStartTime());
    }
}
