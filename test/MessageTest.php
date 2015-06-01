<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Mail;

use stdClass;
use Zend\Mail\Address;
use Zend\Mail\AddressList;
use Zend\Mail\Header;
use Zend\Mail\Headers;
use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Mime;
use Zend\Mime\Part as MimePart;

/**
 * @group      Zend_Mail
 */
class MessageTest extends \PHPUnit_Framework_TestCase
{
    /** @var Message */
    public $message;

    public function setUp()
    {
        $this->message = new Message();
    }

    public function testInvalidByDefault()
    {
        $this->assertFalse($this->message->isValid());
    }

    public function testSetsOrigDateHeaderByDefault()
    {
        $headers = $this->message->getHeaders();
        $this->assertInstanceOf('Zend\Mail\Headers', $headers);
        $this->assertTrue($headers->has('date'));
        $header  = $headers->get('date');
        $date    = date('r');
        $date    = substr($date, 0, 16);
        $test    = $header->getFieldValue();
        $test    = substr($test, 0, 16);
        $this->assertEquals($date, $test);
    }

    public function testAddingFromAddressMarksAsValid()
    {
        $this->message->addFrom('zf-devteam@example.com');
        $this->assertTrue($this->message->isValid());
    }

    public function testHeadersMethodReturnsHeadersObject()
    {
        $headers = $this->message->getHeaders();
        $this->assertInstanceOf('Zend\Mail\Headers', $headers);
    }

    public function testToMethodReturnsAddressListObject()
    {
        $this->message->addTo('zf-devteam@example.com');
        $to = $this->message->getTo();
        $this->assertInstanceOf('Zend\Mail\AddressList', $to);
    }

    public function testToAddressListLivesInHeaders()
    {
        $this->message->addTo('zf-devteam@example.com');
        $to      = $this->message->getTo();
        $headers = $this->message->getHeaders();
        $this->assertInstanceOf('Zend\Mail\Headers', $headers);
        $this->assertTrue($headers->has('to'));
        $header  = $headers->get('to');
        $this->assertSame($header->getAddressList(), $to);
    }

    public function testFromMethodReturnsAddressListObject()
    {
        $this->message->addFrom('zf-devteam@example.com');
        $from = $this->message->getFrom();
        $this->assertInstanceOf('Zend\Mail\AddressList', $from);
    }

    public function testFromAddressListLivesInHeaders()
    {
        $this->message->addFrom('zf-devteam@example.com');
        $from    = $this->message->getFrom();
        $headers = $this->message->getHeaders();
        $this->assertInstanceOf('Zend\Mail\Headers', $headers);
        $this->assertTrue($headers->has('from'));
        $header  = $headers->get('from');
        $this->assertSame($header->getAddressList(), $from);
    }

    public function testCcMethodReturnsAddressListObject()
    {
        $this->message->addCc('zf-devteam@example.com');
        $cc = $this->message->getCc();
        $this->assertInstanceOf('Zend\Mail\AddressList', $cc);
    }

    public function testCcAddressListLivesInHeaders()
    {
        $this->message->addCc('zf-devteam@example.com');
        $cc      = $this->message->getCc();
        $headers = $this->message->getHeaders();
        $this->assertInstanceOf('Zend\Mail\Headers', $headers);
        $this->assertTrue($headers->has('cc'));
        $header  = $headers->get('cc');
        $this->assertSame($header->getAddressList(), $cc);
    }

    public function testBccMethodReturnsAddressListObject()
    {
        $this->message->addBcc('zf-devteam@example.com');
        $bcc = $this->message->getBcc();
        $this->assertInstanceOf('Zend\Mail\AddressList', $bcc);
    }

    public function testBccAddressListLivesInHeaders()
    {
        $this->message->addBcc('zf-devteam@example.com');
        $bcc     = $this->message->getBcc();
        $headers = $this->message->getHeaders();
        $this->assertInstanceOf('Zend\Mail\Headers', $headers);
        $this->assertTrue($headers->has('bcc'));
        $header  = $headers->get('bcc');
        $this->assertSame($header->getAddressList(), $bcc);
    }

    public function testReplyToMethodReturnsAddressListObject()
    {
        $this->message->addReplyTo('zf-devteam@example.com');
        $replyTo = $this->message->getReplyTo();
        $this->assertInstanceOf('Zend\Mail\AddressList', $replyTo);
    }

    public function testReplyToAddressListLivesInHeaders()
    {
        $this->message->addReplyTo('zf-devteam@example.com');
        $replyTo = $this->message->getReplyTo();
        $headers = $this->message->getHeaders();
        $this->assertInstanceOf('Zend\Mail\Headers', $headers);
        $this->assertTrue($headers->has('reply-to'));
        $header  = $headers->get('reply-to');
        $this->assertSame($header->getAddressList(), $replyTo);
    }

    public function testSenderIsNullByDefault()
    {
        $this->assertNull($this->message->getSender());
    }

    public function testSettingSenderCreatesAddressObject()
    {
        $this->message->setSender('zf-devteam@example.com');
        $sender = $this->message->getSender();
        $this->assertInstanceOf('Zend\Mail\Address', $sender);
    }

    public function testCanSpecifyNameWhenSettingSender()
    {
        $this->message->setSender('zf-devteam@example.com', 'ZF DevTeam');
        $sender = $this->message->getSender();
        $this->assertInstanceOf('Zend\Mail\Address', $sender);
        $this->assertEquals('ZF DevTeam', $sender->getName());
    }

    public function testCanProvideAddressObjectWhenSettingSender()
    {
        $sender = new Address('zf-devteam@example.com');
        $this->message->setSender($sender);
        $test = $this->message->getSender();
        $this->assertSame($sender, $test);
    }

    public function testSenderAccessorsProxyToSenderHeader()
    {
        $header = new Header\Sender();
        $this->message->getHeaders()->addHeader($header);
        $address = new Address('zf-devteam@example.com', 'ZF DevTeam');
        $this->message->setSender($address);
        $this->assertSame($address, $header->getAddress());
    }

    public function testCanAddFromAddressUsingName()
    {
        $this->message->addFrom('zf-devteam@example.com', 'ZF DevTeam');
        $addresses = $this->message->getFrom();
        $this->assertEquals(1, count($addresses));
        $address = $addresses->current();
        $this->assertEquals('zf-devteam@example.com', $address->getEmail());
        $this->assertEquals('ZF DevTeam', $address->getName());
    }

    public function testCanAddFromAddressUsingAddressObject()
    {
        $address = new Address('zf-devteam@example.com', 'ZF DevTeam');
        $this->message->addFrom($address);

        $addresses = $this->message->getFrom();
        $this->assertEquals(1, count($addresses));
        $test = $addresses->current();
        $this->assertSame($address, $test);
    }

    public function testCanAddManyFromAddressesUsingArray()
    {
        $addresses = array(
            'zf-devteam@example.com',
            'zf-contributors@example.com' => 'ZF Contributors List',
            new Address('fw-announce@example.com', 'ZF Announce List'),
        );
        $this->message->addFrom($addresses);

        $from = $this->message->getFrom();
        $this->assertEquals(3, count($from));

        $this->assertTrue($from->has('zf-devteam@example.com'));
        $this->assertTrue($from->has('zf-contributors@example.com'));
        $this->assertTrue($from->has('fw-announce@example.com'));
    }

    public function testCanAddManyFromAddressesUsingAddressListObject()
    {
        $list = new AddressList();
        $list->add('zf-devteam@example.com');

        $this->message->addFrom('fw-announce@example.com');
        $this->message->addFrom($list);
        $from = $this->message->getFrom();
        $this->assertEquals(2, count($from));
        $this->assertTrue($from->has('fw-announce@example.com'));
        $this->assertTrue($from->has('zf-devteam@example.com'));
    }

    public function testCanSetFromListFromAddressList()
    {
        $list = new AddressList();
        $list->add('zf-devteam@example.com');

        $this->message->addFrom('fw-announce@example.com');
        $this->message->setFrom($list);
        $from = $this->message->getFrom();
        $this->assertEquals(1, count($from));
        $this->assertFalse($from->has('fw-announce@example.com'));
        $this->assertTrue($from->has('zf-devteam@example.com'));
    }

    public function testCanAddCcAddressUsingName()
    {
        $this->message->addCc('zf-devteam@example.com', 'ZF DevTeam');
        $addresses = $this->message->getCc();
        $this->assertEquals(1, count($addresses));
        $address = $addresses->current();
        $this->assertEquals('zf-devteam@example.com', $address->getEmail());
        $this->assertEquals('ZF DevTeam', $address->getName());
    }

    public function testCanAddCcAddressUsingAddressObject()
    {
        $address = new Address('zf-devteam@example.com', 'ZF DevTeam');
        $this->message->addCc($address);

        $addresses = $this->message->getCc();
        $this->assertEquals(1, count($addresses));
        $test = $addresses->current();
        $this->assertSame($address, $test);
    }

    public function testCanAddManyCcAddressesUsingArray()
    {
        $addresses = array(
            'zf-devteam@example.com',
            'zf-contributors@example.com' => 'ZF Contributors List',
            new Address('fw-announce@example.com', 'ZF Announce List'),
        );
        $this->message->addCc($addresses);

        $cc = $this->message->getCc();
        $this->assertEquals(3, count($cc));

        $this->assertTrue($cc->has('zf-devteam@example.com'));
        $this->assertTrue($cc->has('zf-contributors@example.com'));
        $this->assertTrue($cc->has('fw-announce@example.com'));
    }

    public function testCanAddManyCcAddressesUsingAddressListObject()
    {
        $list = new AddressList();
        $list->add('zf-devteam@example.com');

        $this->message->addCc('fw-announce@example.com');
        $this->message->addCc($list);
        $cc = $this->message->getCc();
        $this->assertEquals(2, count($cc));
        $this->assertTrue($cc->has('fw-announce@example.com'));
        $this->assertTrue($cc->has('zf-devteam@example.com'));
    }

    public function testCanSetCcListFromAddressList()
    {
        $list = new AddressList();
        $list->add('zf-devteam@example.com');

        $this->message->addCc('fw-announce@example.com');
        $this->message->setCc($list);
        $cc = $this->message->getCc();
        $this->assertEquals(1, count($cc));
        $this->assertFalse($cc->has('fw-announce@example.com'));
        $this->assertTrue($cc->has('zf-devteam@example.com'));
    }

    public function testCanAddBccAddressUsingName()
    {
        $this->message->addBcc('zf-devteam@example.com', 'ZF DevTeam');
        $addresses = $this->message->getBcc();
        $this->assertEquals(1, count($addresses));
        $address = $addresses->current();
        $this->assertEquals('zf-devteam@example.com', $address->getEmail());
        $this->assertEquals('ZF DevTeam', $address->getName());
    }

    public function testCanAddBccAddressUsingAddressObject()
    {
        $address = new Address('zf-devteam@example.com', 'ZF DevTeam');
        $this->message->addBcc($address);

        $addresses = $this->message->getBcc();
        $this->assertEquals(1, count($addresses));
        $test = $addresses->current();
        $this->assertSame($address, $test);
    }

    public function testCanAddManyBccAddressesUsingArray()
    {
        $addresses = array(
            'zf-devteam@example.com',
            'zf-contributors@example.com' => 'ZF Contributors List',
            new Address('fw-announce@example.com', 'ZF Announce List'),
        );
        $this->message->addBcc($addresses);

        $bcc = $this->message->getBcc();
        $this->assertEquals(3, count($bcc));

        $this->assertTrue($bcc->has('zf-devteam@example.com'));
        $this->assertTrue($bcc->has('zf-contributors@example.com'));
        $this->assertTrue($bcc->has('fw-announce@example.com'));
    }

    public function testCanAddManyBccAddressesUsingAddressListObject()
    {
        $list = new AddressList();
        $list->add('zf-devteam@example.com');

        $this->message->addBcc('fw-announce@example.com');
        $this->message->addBcc($list);
        $bcc = $this->message->getBcc();
        $this->assertEquals(2, count($bcc));
        $this->assertTrue($bcc->has('fw-announce@example.com'));
        $this->assertTrue($bcc->has('zf-devteam@example.com'));
    }

    public function testCanSetBccListFromAddressList()
    {
        $list = new AddressList();
        $list->add('zf-devteam@example.com');

        $this->message->addBcc('fw-announce@example.com');
        $this->message->setBcc($list);
        $bcc = $this->message->getBcc();
        $this->assertEquals(1, count($bcc));
        $this->assertFalse($bcc->has('fw-announce@example.com'));
        $this->assertTrue($bcc->has('zf-devteam@example.com'));
    }

    public function testCanAddReplyToAddressUsingName()
    {
        $this->message->addReplyTo('zf-devteam@example.com', 'ZF DevTeam');
        $addresses = $this->message->getReplyTo();
        $this->assertEquals(1, count($addresses));
        $address = $addresses->current();
        $this->assertEquals('zf-devteam@example.com', $address->getEmail());
        $this->assertEquals('ZF DevTeam', $address->getName());
    }

    public function testCanAddReplyToAddressUsingAddressObject()
    {
        $address = new Address('zf-devteam@example.com', 'ZF DevTeam');
        $this->message->addReplyTo($address);

        $addresses = $this->message->getReplyTo();
        $this->assertEquals(1, count($addresses));
        $test = $addresses->current();
        $this->assertSame($address, $test);
    }

    public function testCanAddManyReplyToAddressesUsingArray()
    {
        $addresses = array(
            'zf-devteam@example.com',
            'zf-contributors@example.com' => 'ZF Contributors List',
            new Address('fw-announce@example.com', 'ZF Announce List'),
        );
        $this->message->addReplyTo($addresses);

        $replyTo = $this->message->getReplyTo();
        $this->assertEquals(3, count($replyTo));

        $this->assertTrue($replyTo->has('zf-devteam@example.com'));
        $this->assertTrue($replyTo->has('zf-contributors@example.com'));
        $this->assertTrue($replyTo->has('fw-announce@example.com'));
    }

    public function testCanAddManyReplyToAddressesUsingAddressListObject()
    {
        $list = new AddressList();
        $list->add('zf-devteam@example.com');

        $this->message->addReplyTo('fw-announce@example.com');
        $this->message->addReplyTo($list);
        $replyTo = $this->message->getReplyTo();
        $this->assertEquals(2, count($replyTo));
        $this->assertTrue($replyTo->has('fw-announce@example.com'));
        $this->assertTrue($replyTo->has('zf-devteam@example.com'));
    }

    public function testCanSetReplyToListFromAddressList()
    {
        $list = new AddressList();
        $list->add('zf-devteam@example.com');

        $this->message->addReplyTo('fw-announce@example.com');
        $this->message->setReplyTo($list);
        $replyTo = $this->message->getReplyTo();
        $this->assertEquals(1, count($replyTo));
        $this->assertFalse($replyTo->has('fw-announce@example.com'));
        $this->assertTrue($replyTo->has('zf-devteam@example.com'));
    }

    public function testSubjectIsEmptyByDefault()
    {
        $this->assertNull($this->message->getSubject());
    }

    public function testSubjectIsMutable()
    {
        $this->message->setSubject('test subject');
        $subject = $this->message->getSubject();
        $this->assertEquals('test subject', $subject);
    }

    public function testSettingSubjectProxiesToHeader()
    {
        $this->message->setSubject('test subject');
        $headers = $this->message->getHeaders();
        $this->assertInstanceOf('Zend\Mail\Headers', $headers);
        $this->assertTrue($headers->has('subject'));
        $header = $headers->get('subject');
        $this->assertEquals('test subject', $header->getFieldValue());
    }

    public function testBodyIsEmptyByDefault()
    {
        $this->assertNull($this->message->getBody());
    }

    public function testMaySetBodyFromString()
    {
        $this->message->setBody('body');
        $this->assertEquals('body', $this->message->getBody());
    }

    public function testMaySetBodyFromStringSerializableObject()
    {
        $object = new TestAsset\StringSerializableObject('body');
        $this->message->setBody($object);
        $this->assertSame($object, $this->message->getBody());
        $this->assertEquals('body', $this->message->getBodyText());
    }

    public function testMaySetBodyFromMimeMessage()
    {
        $body = new MimeMessage();
        $this->message->setBody($body);
        $this->assertSame($body, $this->message->getBody());
    }

    public function testMaySetNullBody()
    {
        $this->message->setBody(null);
        $this->assertNull($this->message->getBody());
    }

    public static function invalidBodyValues()
    {
        return array(
            array(array('foo')),
            array(true),
            array(false),
            array(new stdClass),
        );
    }

    /**
     * @dataProvider invalidBodyValues
     */
    public function testSettingNonScalarNonMimeNonStringSerializableValueForBodyRaisesException($body)
    {
        $this->setExpectedException('Zend\Mail\Exception\InvalidArgumentException');
        $this->message->setBody($body);
    }

    public function testSettingBodyFromSinglePartMimeMessageSetsAppropriateHeaders()
    {
        $mime = new Mime('foo-bar');
        $part = new MimePart('<b>foo</b>');
        $part->type = 'text/html';
        $body = new MimeMessage();
        $body->setMime($mime);
        $body->addPart($part);

        $this->message->setBody($body);
        $headers = $this->message->getHeaders();
        $this->assertInstanceOf('Zend\Mail\Headers', $headers);

        $this->assertTrue($headers->has('mime-version'));
        $header = $headers->get('mime-version');
        $this->assertEquals('1.0', $header->getFieldValue());

        $this->assertTrue($headers->has('content-type'));
        $header = $headers->get('content-type');
        $this->assertEquals('text/html', $header->getFieldValue());
    }

    public function testSettingUtf8MailBodyFromSinglePartMimeUtf8MessageSetsAppropriateHeaders()
    {
        $mime = new Mime('foo-bar');
        $part = new MimePart('UTF-8 TestString: AaÜüÄäÖöß');
        $part->type = Mime::TYPE_TEXT;
        $part->encoding = Mime::ENCODING_QUOTEDPRINTABLE;
        $part->charset = 'utf-8';
        $body = new MimeMessage();
        $body->setMime($mime);
        $body->addPart($part);

        $this->message->setEncoding('UTF-8');
        $this->message->setBody($body);

        $this->assertContains(
            'Content-Type: text/plain;' . Headers::FOLDING . 'charset="utf-8"' . Headers::EOL
            . 'Content-Transfer-Encoding: quoted-printable' . Headers::EOL,
            $this->message->getHeaders()->toString()
        );
    }

    public function testSettingBodyFromMultiPartMimeMessageSetsAppropriateHeaders()
    {
        $mime = new Mime('foo-bar');
        $text = new MimePart('foo');
        $text->type = 'text/plain';
        $html = new MimePart('<b>foo</b>');
        $html->type = 'text/html';
        $body = new MimeMessage();
        $body->setMime($mime);
        $body->addPart($text);
        $body->addPart($html);

        $this->message->setBody($body);
        $headers = $this->message->getHeaders();
        $this->assertInstanceOf('Zend\Mail\Headers', $headers);

        $this->assertTrue($headers->has('mime-version'));
        $header = $headers->get('mime-version');
        $this->assertEquals('1.0', $header->getFieldValue());

        $this->assertTrue($headers->has('content-type'));
        $header = $headers->get('content-type');
        $this->assertEquals("multipart/mixed;\r\n boundary=\"foo-bar\"", $header->getFieldValue());
    }

    public function testRetrievingBodyTextFromMessageWithMultiPartMimeBodyReturnsMimeSerialization()
    {
        $mime = new Mime('foo-bar');
        $text = new MimePart('foo');
        $text->type = 'text/plain';
        $html = new MimePart('<b>foo</b>');
        $html->type = 'text/html';
        $body = new MimeMessage();
        $body->setMime($mime);
        $body->addPart($text);
        $body->addPart($html);

        $this->message->setBody($body);

        $text = $this->message->getBodyText();
        $this->assertEquals($body->generateMessage(Headers::EOL), $text);
        $this->assertContains('--foo-bar', $text);
        $this->assertContains('--foo-bar--', $text);
        $this->assertContains('Content-Type: text/plain', $text);
        $this->assertContains('Content-Type: text/html', $text);
    }

    public function testEncodingIsAsciiByDefault()
    {
        $this->assertEquals('ASCII', $this->message->getEncoding());
    }

    public function testEncodingIsMutable()
    {
        $this->message->setEncoding('UTF-8');
        $this->assertEquals('UTF-8', $this->message->getEncoding());
    }

    public function testMessageReturnsNonEncodedSubject()
    {
        $this->message->setSubject('This is a subject');
        $this->message->setEncoding('UTF-8');
        $this->assertEquals('This is a subject', $this->message->getSubject());
    }

    public function testSettingNonAsciiEncodingForcesMimeEncodingOfSomeHeaders()
    {
        $this->message->addTo('zf-devteam@example.com', 'ZF DevTeam');
        $this->message->addFrom('matthew@example.com', "Matthew Weier O'Phinney");
        $this->message->addCc('zf-contributors@example.com', 'ZF Contributors List');
        $this->message->addBcc('zf-crteam@example.com', 'ZF CR Team');
        $this->message->setSubject('This is a subject');
        $this->message->setEncoding('UTF-8');

        $test = $this->message->getHeaders()->toString();

        $expected = '=?UTF-8?B?WkYgRGV2VGVhbQ==?=';
        $this->assertContains($expected, $test);
        $this->assertContains('<zf-devteam@example.com>', $test);

        $expected = "=?UTF-8?B?TWF0dGhldyBXZWllciBPJ1BoaW5uZXk=?=";
        $this->assertContains($expected, $test, $test);
        $this->assertContains('<matthew@example.com>', $test);

        $expected = '=?UTF-8?B?WkYgQ29udHJpYnV0b3JzIExpc3Q=?=';
        $this->assertContains($expected, $test);
        $this->assertContains('<zf-contributors@example.com>', $test);

        $expected = '=?UTF-8?B?WkYgQ1IgVGVhbQ==?=';
        $this->assertContains($expected, $test);
        $this->assertContains('<zf-crteam@example.com>', $test);

        $expected = 'Subject: =?UTF-8?B?VGhpcyBpcyBhIHN1YmplY3Q=?=';
        $this->assertContains($expected, $test);
    }

    /**
     * @group ZF2-507
     */
    public function testDefaultDateHeaderEncodingIsAlwaysAscii()
    {
        $this->message->setEncoding('utf-8');
        $headers = $this->message->getHeaders();
        $header  = $headers->get('date');
        $date    = date('r');
        $date    = substr($date, 0, 16);
        $test    = $header->getFieldValue();
        $test    = substr($test, 0, 16);
        $this->assertEquals($date, $test);
    }

    public function testRestoreFromSerializedString()
    {
        $this->message->addTo('zf-devteam@example.com', 'ZF DevTeam');
        $this->message->addFrom('matthew@example.com', "Matthew Weier O'Phinney");
        $this->message->addCc('zf-contributors@example.com', 'ZF Contributors List');
        $this->message->setSubject('This is a subject');
        $this->message->setBody('foo');
        $serialized      = $this->message->toString();
        $restoredMessage = Message::fromString($serialized);
        $this->assertEquals($serialized, $restoredMessage->toString());
    }

    /**
     * @group ZF2-5962
     */
    public function testPassEmptyArrayIntoSetPartsOfMimeMessageShouldReturnEmptyBodyString()
    {
        $mimeMessage = new MimeMessage();
        $mimeMessage->setParts(array());

        $this->message->setBody($mimeMessage);
        $this->assertEquals('', $this->message->getBodyText());
    }

    public function messageRecipients()
    {
        return array(
            'setFrom' => array('setFrom'),
            'addFrom' => array('addFrom'),
            'setTo' => array('setTo'),
            'addTo' => array('addTo'),
            'setCc' => array('setCc'),
            'addCc' => array('addCc'),
            'setBcc' => array('setBcc'),
            'addBcc' => array('addBcc'),
            'setReplyTo' => array('setReplyTo'),
            'setSender' => array('setSender'),
        );
    }

    /**
     * @group ZF2015-04
     * @dataProvider messageRecipients
     */
    public function testRaisesExceptionWhenAttemptingToSerializeMessageWithCRLFInjectionViaHeader($recipientMethod)
    {
        $subject = array(
            'test1',
            'Content-Type: text/html; charset = "iso-8859-1"',
            '',
            '<html><body><iframe src="http://example.com/"></iframe></body></html> <!--',
        );
        $this->setExpectedException('Zend\Mail\Exception\InvalidArgumentException');
        $this->message->{$recipientMethod}(implode(Headers::EOL, $subject));
    }

    /**
     * @group ZF2015-04
     */
    public function testDetectsCRLFInjectionViaSubject()
    {
        $subject = array(
            'test1',
            'Content-Type: text/html; charset = "iso-8859-1"',
            '',
            '<html><body><iframe src="http://example.com/"></iframe></body></html> <!--',
        );
        $this->message->setSubject(implode(Headers::EOL, $subject));

        $serializedHeaders = $this->message->getHeaders()->toString();
        $this->assertNotContains("\r\n<html>", $serializedHeaders);
    }

    public function testHeaderUnfoldingWorksAsExpectedForMultipartMessages()
    {
        $text = new MimePart('Test content');
        $text->type = Mime::TYPE_TEXT;
        $text->encoding = Mime::ENCODING_QUOTEDPRINTABLE;
        $text->disposition = Mime::DISPOSITION_INLINE;
        $text->charset = 'UTF-8';

        $html = new MimePart('<b>Test content</b>');
        $html->type = Mime::TYPE_HTML;
        $html->encoding = Mime::ENCODING_QUOTEDPRINTABLE;
        $html->disposition = Mime::DISPOSITION_INLINE;
        $html->charset = 'UTF-8';

        $multipartContent = new MimeMessage();
        $multipartContent->addPart($text);
        $multipartContent->addPart($html);

        $multipartPart = new MimePart($multipartContent->generateMessage());
        $multipartPart->charset = 'UTF-8';
        $multipartPart->type = 'multipart/alternative';
        $multipartPart->boundary = $multipartContent->getMime()->boundary();

        $message = new MimeMessage();
        $message->addPart($multipartPart);

        $this->message->getHeaders()->addHeaderLine('Content-Transfer-Encoding', Mime::ENCODING_QUOTEDPRINTABLE);
        $this->message->setBody($message);

        $contentType = $this->message->getHeaders()->get('Content-Type');
        $this->assertInstanceOf('Zend\Mail\Header\ContentType', $contentType);
        $this->assertContains('multipart/alternative', $contentType->getFieldValue());
        $this->assertContains($multipartContent->getMime()->boundary(), $contentType->getFieldValue());
    }

    public function getUglyHeaderMail()
    {
        return <<<EOD
To: =?utf-8?B?QUFBLCB0ZXN0IHdpdGggw6nDoCTDuQ==?= <aaaa@bbbb.com>,,
 foo@example.com,
To: bar@example.com
Cc: 
Subject: ugly headers
Date: Sun, 01 Jan 2000 00:00:00 +0000
From: baz@example.com
ContENTtype: text/plain
Message-ID: <aaaaa@mail.example.com>

We should support:
Duplicated mail headers (see To)
Too many commas in headers (see To)
Comma in encoded word (see To)
Empty mail headers (see Cc)
Malformatted headers (see ContENTtype)
EOD;
    }

    public function testParseUglyHeaderMail()
    {
        $message1 = Message::fromString($this->getUglyHeaderMail());
        $this->assertEquals(3, $message1->getTo()->count(), "There is 3 email addresses in the two 'To' headers");
        $this->assertEquals(0, $message1->getCc()->count(), "There is no email address in the empty 'Cc' header");
        $this->assertEquals("text/plain", $message1->getHeaders()->get('Content-Type')->getFieldValue());
    }

    public function testStableParsing()
    {
        $message1 = Message::fromString($this->getUglyHeaderMail());
        $raw1 = $message1->toString();
        $message2 = Message::fromString($raw1);
        $raw2 = $message2->toString();
        $this->assertEquals($raw1, $raw2, "Parsing isn't stable, we should be able to parse Message->toString() output and get same result");
    }
}
