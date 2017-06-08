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
use Zend\Mail\Header\Exception;
use Zend\Mail\Header\GenericHeader;

/**
 * @covers Zend\Mail\Header\GenericHeader<extended>
 */
class GenericHeaderTest extends TestCase
{
    /**
     * @group ZF2015-04
     */
    public function testSplitHeaderLineRaisesExceptionOnInvalidHeader()
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        GenericHeader::splitHeaderLine(
            'Content-Type' . chr(32) . ': text/html; charset = "iso-8859-1"' . "\nThis is a test"
        );
    }

    public function fieldNames()
    {
        return [
            'append-chr-13'  => ["Subject" . chr(13)],
            'append-chr-127' => ["Subject" . chr(127)],
        ];
    }

    /**
     * @dataProvider fieldNames
     * @group ZF2015-04
     */
    public function testRaisesExceptionOnInvalidFieldName($fieldName)
    {
        $header = new GenericHeader();
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('name');
        $header->setFieldName($fieldName);
    }

    public function fieldValues()
    {
        return [
            'empty-lines'             => ["\n\n\r\n\r\n\n"],
            'trailing-newlines'       => ["Value\n\n\r\n\r\n\n"],
            'leading-newlines'        => ["\n\n\r\n\r\n\nValue"],
            'surrounding-newlines'    => ["\n\n\r\n\r\n\nValue\n\n\r\n\r\n\n"],
            'split-value'             => ["Some\n\n\r\n\r\n\nValue"],
            'leading-split-value'     => ["\n\n\r\n\r\n\nSome\n\n\r\n\r\n\nValue"],
            'trailing-split-value'    => ["Some\n\n\r\n\r\n\nValue\n\n\r\n\r\n\n"],
            'surrounding-split-value' => ["\n\n\r\n\r\n\nSome\n\n\r\n\r\n\nValue\n\n\r\n\r\n\n"],
        ];
    }

    /**
     * @dataProvider fieldValues
     * @group ZF2015-04
     * @param string $fieldValue
     */
    public function testCRLFsequencesAreEncodedOnToString($fieldValue)
    {
        $header = new GenericHeader('Foo');
        $header->setFieldValue($fieldValue);

        $serialized = $header->toString();
        $this->assertNotContains("\n", $serialized);
        $this->assertNotContains("\r", $serialized);
    }

    /**
     * @dataProvider validFieldValuesProvider
     * @group ZF2015-04
     * @param string $decodedValue
     * @param string $encodedValue
     * @param string $encoding
     */
    public function testParseValidSubjectHeader($decodedValue, $encodedValue, $encoding)
    {
        $header = GenericHeader::fromString('Foo:' . $encodedValue);

        $this->assertEquals($decodedValue, $header->getFieldValue());
        $this->assertEquals($encoding, $header->getEncoding());
    }

    /**
     * @dataProvider validFieldValuesProvider
     * @group ZF2015-04
     * @param string $decodedValue
     * @param string $encodedValue
     * @param string $encoding
     */
    public function testSetFieldValueValidValue($decodedValue, $encodedValue, $encoding)
    {
        $header = new GenericHeader('Foo');
        $header->setFieldValue($decodedValue);

        $this->assertEquals($decodedValue, $header->getFieldValue());
        $this->assertEquals('Foo: ' . $encodedValue, $header->toString());
        $this->assertEquals($encoding, $header->getEncoding());
    }

    public function validFieldValuesProvider()
    {
        return [
            // Description => [decoded format, encoded format, encoding],
            //'Empty' => array('', '', 'ASCII'),

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

    /**
     * @group ZF2015-04
     */
    public function testCastingToStringHandlesContinuationsProperly()
    {
        $encoded = '=?UTF-8?Q?foo=0D=0A=20bar?=';
        $raw = "foo\r\n bar";

        $header = new GenericHeader('Foo');
        $header->setFieldValue($raw);

        $this->assertEquals($raw, $header->getFieldValue());
        $this->assertEquals($encoded, $header->getFieldValue(GenericHeader::FORMAT_ENCODED));
        $this->assertEquals('Foo: ' . $encoded, $header->toString());
    }

    public function testAllowZeroInHeaderValueInConstructor()
    {
        $header = new GenericHeader('Foo', 0);
        $this->assertEquals(0, $header->getFieldValue());
        $this->assertEquals('Foo: 0', $header->toString());
    }
}
