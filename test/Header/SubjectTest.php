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
 * @covers Zend\Mail\Header\Subject<extended>
 */
class SubjectTest extends TestCase
{
    public function testHeaderFolding()
    {
        $string  = str_repeat('foobarblahblahblah baz bat', 10);
        $subject = new Header\Subject();
        $subject->setSubject($string);

        $expected = wordwrap($string, 78, "\r\n ");
        $test     = $subject->getFieldValue(Header\HeaderInterface::FORMAT_ENCODED);
        $this->assertEquals($expected, $test);
    }

    /**
     * @dataProvider validSubjectValuesProvider
     * @group ZF2015-04
     * @param string $decodedValue
     * @param string $encodedValue
     * @param string $encoding
     */
    public function testParseValidSubjectHeader($decodedValue, $encodedValue, $encoding)
    {
        $header = Header\Subject::fromString('Subject:' . $encodedValue);

        $this->assertEquals($decodedValue, $header->getFieldValue());
        $this->assertEquals($encoding, $header->getEncoding());
    }

    /**
     * @dataProvider invalidSubjectValuesProvider
     * @group ZF2015-04
     * @param string $decodedValue
     * @param string $expectedException
     * @param string|null $expectedExceptionMessage
     */
    public function testParseInvalidSubjectHeaderThrowException(
        $decodedValue,
        $expectedException,
        $expectedExceptionMessage
    ) {
        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedExceptionMessage);
        Header\Subject::fromString('Subject:' . $decodedValue);
    }

    /**
     * @dataProvider validSubjectValuesProvider
     * @group ZF2015-04
     * @param string $decodedValue
     * @param string $encodedValue
     * @param string $encoding
     */
    public function testSetSubjectValidValue($decodedValue, $encodedValue, $encoding)
    {
        $header = new Header\Subject();
        $header->setSubject($decodedValue);

        $this->assertEquals($decodedValue, $header->getFieldValue());
        $this->assertEquals('Subject: ' . $encodedValue, $header->toString());
        $this->assertEquals($encoding, $header->getEncoding());
    }

    public function validSubjectValuesProvider()
    {
        return [
            // Description => [decoded format, encoded format, encoding],
            'Empty' => ['', '', 'ASCII'],

            // Encoding cases
            'ASCII charset' => ['azAZ09-_', 'azAZ09-_', 'ASCII'],
            'UTF-8 charset' => ['ázÁZ09-_', '=?UTF-8?Q?=C3=A1z=C3=81Z09-=5F?=', 'UTF-8'],

            // CRLF @group ZF2015-04 cases
            'newline' => ["xxx yyy\n", '=?UTF-8?Q?xxx=20yyy=0A?=', 'UTF-8'],
            'cr-lf' => ["xxx yyy\r\n", '=?UTF-8?Q?xxx=20yyy=0D=0A?=', 'UTF-8'],
            'cr-lf-wsp' => ["xxx yyy\r\n\r\n", '=?UTF-8?Q?xxx=20yyy=0D=0A=0D=0A?=', 'UTF-8'],
            'multiline' => ["xxx\r\ny\r\nyy", '=?UTF-8?Q?xxx=0D=0Ay=0D=0Ayy?=', 'UTF-8'],
        ];
    }

    public function invalidSubjectValuesProvider()
    {
        $invalidArgumentException = 'Zend\Mail\Header\Exception\InvalidArgumentException';
        $invalidHeaderValueDetected = 'Invalid header value detected';

        return [
            // Description => [decoded format, exception class, exception message],
            'newline' => ["xxx yyy\n", $invalidArgumentException, $invalidHeaderValueDetected],
            'cr-lf' => ["xxx yyy\r\n", $invalidArgumentException, $invalidHeaderValueDetected],
            'cr-lf-wsp' => ["xxx yyy\r\n\r\n", $invalidArgumentException, $invalidHeaderValueDetected],
            'multiline' => ["xxx\r\ny\r\nyy", $invalidArgumentException, $invalidHeaderValueDetected],
        ];
    }
}
