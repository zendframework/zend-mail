<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Mail\Header;

use PHPUnit\Framework\TestCase;
use Zend\Mail\Header;

/**
 * @covers Zend\Mail\Header\Received<extended>
 */
class ReceivedTest extends TestCase
{
    public function testFromStringCreatesValidReceivedHeader()
    {
        $receivedHeader = Header\Received::fromString('Received: xxx');
        $this->assertInstanceOf('Zend\Mail\Header\HeaderInterface', $receivedHeader);
        $this->assertInstanceOf('Zend\Mail\Header\Received', $receivedHeader);
    }

    public function testGetFieldNameReturnsHeaderName()
    {
        $receivedHeader = new Header\Received();
        $this->assertEquals('Received', $receivedHeader->getFieldName());
    }

    public function testReceivedGetFieldValueReturnsProperValue()
    {
        $this->markTestIncomplete('Received needs to be completed');

        $receivedHeader = new Header\Received();
        $this->assertEquals('xxx', $receivedHeader->getFieldValue());
    }

    public function testReceivedToStringReturnsHeaderFormattedString()
    {
        $this->markTestIncomplete('Received needs to be completed');

        $receivedHeader = new Header\Received();

        // @todo set some values, then test output
        $this->assertEmpty('Received: xxx', $receivedHeader->toString());
    }

    /** Implementation specific tests here */

    public function headerLines()
    {
        return [
            'newline'      => ["Received: xx\nx"],
            'cr-lf'        => ["Received: xxx\r\n"],
            'cr-lf-fold'   => ["Received: xxx\r\n\r\n zzz"],
            'cr-lf-x2'     => ["Received: xx\r\n\r\nx"],
            'multiline'    => ["Received: x\r\nx\r\nx"],
        ];
    }

    /**
     * @dataProvider headerLines
     * @group ZF2015-04
     */
    public function testRaisesExceptionViaFromStringOnDetectionOfCrlfInjection($header)
    {
        $this->expectException(Header\Exception\InvalidArgumentException::class);
        $received = Header\Received::fromString($header);
    }

    public function invalidValues()
    {
        return [
            'newline'      => ["xx\nx"],
            'cr-lf'        => ["xxx\r\n"],
            'cr-lf-wsp'    => ["xx\r\n\r\nx"],
            'multiline'    => ["x\r\nx\r\nx"],
        ];
    }

    /**
     * @dataProvider invalidValues
     * @group ZF2015-04
     */
    public function testConstructorRaisesExceptionOnValueWithCRLFInjectionAttempt($value)
    {
        $this->expectException(Header\Exception\InvalidArgumentException::class);
        new Header\Received($value);
    }
}
