<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Mail\Transport;

use Zend\Mail\Headers;
use Zend\Mail\Message;
use Zend\Mail\Transport\Smtp;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Mail\Transport\Envelope;
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

    public function tearDown()
    {
        $this->connection->setRaiseExpectExceptonOnNextQuit(false);
    }

    public function getMessage()
    {
        $message = new Message();
        $message->addTo('zf-devteam@zend.com', 'ZF DevTeam')
                ->addCc('matthew@zend.com')
                ->addBcc('zf-crteam@lists.zend.com', 'CR-Team, ZF Project')
                ->addFrom([
                    'zf-devteam@zend.com',
                    'matthew@zend.com' => 'Matthew',
                ])
                ->setSender('ralph.schindler@zend.com', 'Ralph Schindler')
                ->setSubject('Testing Zend\Mail\Transport\Sendmail')
                ->setBody('This is only a test.');
        $message->getHeaders()->addHeaders([
            'X-Foo-Bar' => 'Matthew',
        ]);
        return $message;
    }

    /**
     *  Per RFC 2822 3.6
     */
    public function testSendMailWithoutMinimalHeaders()
    {
        $this->setExpectedException(
            'Zend\Mail\Transport\Exception\RuntimeException',
            'transport expects either a Sender or at least one From address in the Message; none provided'
        );
        $message = new Message();
        $this->transport->send($message);
    }

    /**
     *  Per RFC 2821 3.3 (page 18)
     *  - RCPT (recipient) must be called before DATA (headers or body)
     */
    public function testSendMailWithoutRecipient()
    {
        $this->setExpectedException(
            'Zend\Mail\Transport\Exception\RuntimeException',
            'at least one recipient if the message has at least one header or body'
        );
        $message = new Message();
        $message->setSender('ralph.schindler@zend.com', 'Ralph Schindler');
        $this->transport->send($message);
    }

    public function testSendMailWithEnvelopeFrom()
    {
        $message = $this->getMessage();
        $envelope = new Envelope([
            'from' => 'mailer@lists.zend.com',
        ]);
        $this->transport->setEnvelope($envelope);
        $this->transport->send($message);

        $data = $this->connection->getLog();
        $this->assertContains('MAIL FROM:<mailer@lists.zend.com>', $data);
        $this->assertContains('RCPT TO:<matthew@zend.com>', $data);
        $this->assertContains('RCPT TO:<zf-crteam@lists.zend.com>', $data);
        $this->assertContains("From: zf-devteam@zend.com,\r\n Matthew <matthew@zend.com>\r\n", $data);
    }

    public function testSendMailWithEnvelopeTo()
    {
        $message = $this->getMessage();
        $envelope = new Envelope([
            'to' => 'users@lists.zend.com',
        ]);
        $this->transport->setEnvelope($envelope);
        $this->transport->send($message);

        $data = $this->connection->getLog();
        $this->assertContains('MAIL FROM:<ralph.schindler@zend.com>', $data);
        $this->assertContains('RCPT TO:<users@lists.zend.com>', $data);
        $this->assertContains('To: ZF DevTeam <zf-devteam@zend.com>', $data);
    }

    public function testSendMailWithEnvelope()
    {
        $message = $this->getMessage();
        $to = ['users@lists.zend.com', 'dev@lists.zend.com'];
        $envelope = new Envelope([
            'from' => 'mailer@lists.zend.com',
            'to' => $to,
        ]);
        $this->transport->setEnvelope($envelope);
        $this->transport->send($message);

        $this->assertEquals($to, $this->connection->getRecipients());

        $data = $this->connection->getLog();
        $this->assertContains('MAIL FROM:<mailer@lists.zend.com>', $data);
        $this->assertContains('RCPT TO:<users@lists.zend.com>', $data);
        $this->assertContains('RCPT TO:<dev@lists.zend.com>', $data);
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
        $expectedMessage = "Date: Sun, 10 Jun 2012 20:07:24 +0200\r\n"
                           . "Sender: Ralph Schindler <ralph.schindler@zend.com>\r\n"
                           . "To: ZF DevTeam <zf-devteam@zend.com>\r\n"
                           . "\r\n"
                           . "testSendMailWithoutMinimalHeaders";

        $this->transport->send($message);

        $this->assertContains($expectedMessage, $this->connection->getLog());
    }

    public function testReceivesMailArtifacts()
    {
        $message = $this->getMessage();
        $this->transport->send($message);

        $expectedRecipients = ['zf-devteam@zend.com', 'matthew@zend.com', 'zf-crteam@lists.zend.com'];
        $this->assertEquals($expectedRecipients, $this->connection->getRecipients());

        $data = $this->connection->getLog();
        $this->assertContains('MAIL FROM:<ralph.schindler@zend.com>', $data);
        $this->assertContains('To: ZF DevTeam <zf-devteam@zend.com>', $data);
        $this->assertContains('Subject: Testing Zend\Mail\Transport\Sendmail', $data);
        $this->assertContains("Cc: matthew@zend.com\r\n", $data);
        $this->assertNotContains("Bcc: \"CR-Team, ZF Project\" <zf-crteam@lists.zend.com>\r\n", $data);
        $this->assertContains("From: zf-devteam@zend.com,\r\n Matthew <matthew@zend.com>\r\n", $data);
        $this->assertContains("X-Foo-Bar: Matthew\r\n", $data);
        $this->assertContains("Sender: Ralph Schindler <ralph.schindler@zend.com>\r\n", $data);
        $this->assertContains("\r\n\r\nThis is only a test.", $data, $data);
    }

    public function testCanUseAuthenticationExtensionsViaPluginManager()
    {
        $options    = new SmtpOptions([
            'connection_class' => 'login',
        ]);
        $transport  = new Smtp($options);
        $connection = $transport->plugin($options->getConnectionClass(), [
            'username' => 'matthew',
            'password' => 'password',
            'host'     => 'localhost',
        ]);
        $this->assertInstanceOf('Zend\Mail\Protocol\Smtp\Auth\Login', $connection);
        $this->assertEquals('matthew', $connection->getUsername());
        $this->assertEquals('password', $connection->getPassword());
    }

    public function testSetAutoDisconnect()
    {
        $this->transport->setAutoDisconnect(false);
        $this->assertFalse($this->transport->getAutoDisconnect());
    }

    public function testGetDefaultAutoDisconnectValue()
    {
        $this->assertTrue($this->transport->getAutoDisconnect());
    }

    public function testAutoDisconnectTrue()
    {
        $this->connection->connect();
        unset($this->transport);
        $this->assertFalse($this->connection->hasSession());
    }

    public function testAutoDisconnectFalse()
    {
        $this->connection->connect();
        $this->transport->setAutoDisconnect(false);
        unset($this->transport);
        $this->assertTrue($this->connection->isConnected());
    }

    public function testDisconnect()
    {
        $this->connection->connect();
        $this->assertTrue($this->connection->isConnected());
        $this->transport->disconnect();
        $this->assertFalse($this->connection->isConnected());
    }

    public function testDisconnectSendReconnects()
    {
        $this->assertFalse($this->connection->hasSession());
        $this->transport->send($this->getMessage());
        $this->assertTrue($this->connection->hasSession());
        $this->connection->disconnect();

        $this->assertFalse($this->connection->hasSession());
        $this->transport->send($this->getMessage());
        $this->assertTrue($this->connection->hasSession());
    }

    /*
     * @see https://github.com/zendframework/zend-mail/pull/27
     */
    public function testHandleReuseTimeLimit()
    {
        $message = $this->getMessage();
        $options = new SmtpOptions([
            'reuse_time_limit' => 0,
        ]);
        $transport = new Smtp($options);
        $transport->setConnection($this->connection);
        $transport->send($message);
        $this->connection->setRaiseExpectExceptonOnNextQuit(true);
        $transport->send($message);
        $this->assertTrue($this->connection->calledQuit);
    }

    public function testDefaultReuseTimeLimitIsUnlimited()
    {
        $message = $this->getMessage();
        $this->transport->send($message);
        $this->connection->setRaiseExpectExceptonOnNextQuit(true);
        $this->transport->send($message);
        $this->assertFalse($this->connection->calledQuit);
    }
}
