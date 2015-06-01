<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Mail\Header;

use Zend\Mail\Address;
use Zend\Mail\AddressList;
use Zend\Mail\Header\Bcc;
use Zend\Mail\Header\Cc;
use Zend\Mail\Header\From;
use Zend\Mail\Header\ReplyTo;
use Zend\Mail\Header\To;

/**
 * @group      Zend_Mail
 */
class AddressListHeaderTest extends \PHPUnit_Framework_TestCase
{
    public static function getHeaderInstances()
    {
        return array(
            array(new Bcc(), 'Bcc'),
            array(new Cc(), 'Cc'),
            array(new From(), 'From'),
            array(new ReplyTo(), 'Reply-To'),
            array(new To(), 'To'),
        );
    }

    /**
     * @dataProvider getHeaderInstances
     */
    public function testConcreteHeadersExtendAbstractAddressListHeader($header)
    {
        $this->assertInstanceOf('Zend\Mail\Header\AbstractAddressList', $header);
    }

    /**
     * @dataProvider getHeaderInstances
     */
    public function testConcreteHeaderFieldNamesAreDiscrete($header, $type)
    {
        $this->assertEquals($type, $header->getFieldName());
    }

    /**
     * @dataProvider getHeaderInstances
     */
    public function testConcreteHeadersComposeAddressLists($header)
    {
        $list = $header->getAddressList();
        $this->assertInstanceOf('Zend\Mail\AddressList', $list);
    }

    public function testFieldValueIsEmptyByDefault()
    {
        $header = new To();
        $this->assertEquals('', $header->getFieldValue());
    }

    public function testFieldValueIsCreatedFromAddressList()
    {
        $header = new To();
        $list   = $header->getAddressList();
        $this->populateAddressList($list);
        $expected = $this->getExpectedFieldValue();
        $this->assertEquals($expected, $header->getFieldValue());
    }

    public function populateAddressList(AddressList $list)
    {
        $address = new Address('zf-devteam@zend.com', 'ZF DevTeam');
        $list->add($address);
        $list->add('zf-contributors@lists.zend.com');
        $list->add('fw-announce@lists.zend.com', 'ZF Announce List');
        $list->add('first@last.zend.com', 'Last, First');
    }

    public function getExpectedFieldValue()
    {
        return "ZF DevTeam <zf-devteam@zend.com>,\r\n zf-contributors@lists.zend.com,\r\n ZF Announce List <fw-announce@lists.zend.com>,\r\n \"Last, First\" <first@last.zend.com>";
    }

    /**
     * @dataProvider getHeaderInstances
     */
    public function testStringRepresentationIncludesHeaderAndFieldValue($header, $type)
    {
        $this->populateAddressList($header->getAddressList());
        $expected = sprintf('%s: %s', $type, $this->getExpectedFieldValue());
        $this->assertEquals($expected, $header->toString());
    }

    public function getStringHeaders()
    {
        $value = $this->getExpectedFieldValue();
        return array(
            'cc'       => array('Cc: ' . $value, 'Zend\Mail\Header\Cc'),
            'bcc'      => array('Bcc: ' . $value, 'Zend\Mail\Header\Bcc'),
            'from'     => array('From: ' . $value, 'Zend\Mail\Header\From'),
            'reply-to' => array('Reply-To: ' . $value, 'Zend\Mail\Header\ReplyTo'),
            'to'       => array('To: ' . $value, 'Zend\Mail\Header\To'),
        );
    }

    /**
     * @dataProvider getStringHeaders
     */
    public function testDeserializationFromString($headerLine, $class)
    {
        $callback = sprintf('%s::fromString', $class);
        $header   = call_user_func($callback, $headerLine);
        $this->assertInstanceOf($class, $header);
        $list = $header->getAddressList();
        $this->assertEquals(4, count($list));
        $this->assertTrue($list->has('zf-devteam@zend.com'));
        $this->assertTrue($list->has('zf-contributors@lists.zend.com'));
        $this->assertTrue($list->has('fw-announce@lists.zend.com'));
        $this->assertTrue($list->has('first@last.zend.com'));
        $address = $list->get('zf-devteam@zend.com');
        $this->assertEquals('ZF DevTeam', $address->getName());
        $address = $list->get('zf-contributors@lists.zend.com');
        $this->assertNull($address->getName());
        $address = $list->get('fw-announce@lists.zend.com');
        $this->assertEquals('ZF Announce List', $address->getName());
        $address = $list->get('first@last.zend.com');
        $this->assertEquals('Last, First', $address->getName());
    }

    public function getStringHeadersWithNoWhitespaceSeparator()
    {
        $value = $this->getExpectedFieldValue();
        return array(
            'cc'       => array('Cc:' . $value, 'Zend\Mail\Header\Cc'),
            'bcc'      => array('Bcc:' . $value, 'Zend\Mail\Header\Bcc'),
            'from'     => array('From:' . $value, 'Zend\Mail\Header\From'),
            'reply-to' => array('Reply-To:' . $value, 'Zend\Mail\Header\ReplyTo'),
            'to'       => array('To:' . $value, 'Zend\Mail\Header\To'),
        );
    }

    /**
     * @group 3789
     * @dataProvider getStringHeadersWithNoWhitespaceSeparator
     */
    public function testAllowsNoWhitespaceBetweenHeaderAndValue($headerLine, $class)
    {
        $callback = sprintf('%s::fromString', $class);
        $header   = call_user_func($callback, $headerLine);
        $this->assertInstanceOf($class, $header);
        $list = $header->getAddressList();
        $this->assertEquals(4, count($list));
        $this->assertTrue($list->has('zf-devteam@zend.com'));
        $this->assertTrue($list->has('zf-contributors@lists.zend.com'));
        $this->assertTrue($list->has('fw-announce@lists.zend.com'));
        $this->assertTrue($list->has('first@last.zend.com'));
        $address = $list->get('zf-devteam@zend.com');
        $this->assertEquals('ZF DevTeam', $address->getName());
        $address = $list->get('zf-contributors@lists.zend.com');
        $this->assertNull($address->getName());
        $address = $list->get('fw-announce@lists.zend.com');
        $this->assertEquals('ZF Announce List', $address->getName());
        $address = $list->get('first@last.zend.com');
        $this->assertEquals('Last, First', $address->getName());
    }

    public function getStringEmptyHeaders()
    {
        return array(
            'cc'       => array('Cc:', 'Zend\Mail\Header\Cc'),
            'bcc'      => array('Bcc:', 'Zend\Mail\Header\Bcc'),
            'from'     => array('From:', 'Zend\Mail\Header\From'),
            'reply-to' => array('Reply-To:', 'Zend\Mail\Header\ReplyTo'),
            'to'       => array('To:', 'Zend\Mail\Header\To'),
        );
    }

    /**
     * @dataProvider getStringEmptyHeaders
     */
    public function testAllowEmptyHeader($headerLine, $class) {
        $callback = sprintf('%s::fromString', $class);
        $header   = call_user_func($callback, $headerLine);
        $this->assertInstanceOf($class, $header);
        $list = $header->getAddressList();
        $this->assertEquals(0, count($list));
    }

    public function testEncodedCommaInHeader() {
        $header = \Zend\Mail\Header\To::fromString("To: =?utf-8?B?QUFBLCB0ZXN0IHdpdGggw6nDoCTDuQ==?= <aaaa@bbbb.com>, cc@dd.com");
        $this->assertInstanceOf('Zend\Mail\Header\To', $header);
        $list = $header->getAddressList();
        $this->assertEquals(2, count($list));
        $this->assertTrue($list->has('aaaa@bbbb.com'));
        $this->assertTrue($list->has('cc@dd.com'));
        $address = $list->get('aaaa@bbbb.com');
        $this->assertEquals('AAA, test with éà$ù', $address->getName());
    }

    public function testTooManyCommaInHeader() {
        $header = \Zend\Mail\Header\To::fromString("To: <aaaa@bbbb.com>, ,cc@dd.com,");
        $this->assertInstanceOf('Zend\Mail\Header\To', $header);
        $list = $header->getAddressList();
        $this->assertEquals(2, count($list));
        $this->assertTrue($list->has('aaaa@bbbb.com'));
        $this->assertTrue($list->has('cc@dd.com'));
    }
}
