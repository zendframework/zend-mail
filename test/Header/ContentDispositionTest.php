<?php
/**
 * @see       https://github.com/zendframework/zend-mail for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-mail/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Mail\Header;

use PHPUnit\Framework\TestCase;
use Zend\Mail\Header\ContentDisposition;
use Zend\Mail\Header\Exception\InvalidArgumentException;
use Zend\Mail\Header\HeaderInterface;
use Zend\Mail\Header\UnstructuredInterface;

/**
 * @group Zend_Mail
 * @covers Zend\Mail\Header\ContentDisposition<extended>
 */
class ContentDispositionTest extends TestCase
{
    public function testImplementsHeaderInterface()
    {
        $header = new ContentDisposition();

        $this->assertInstanceOf(UnstructuredInterface::class, $header);
        $this->assertInstanceOf(HeaderInterface::class, $header);
    }

    public function testTrailingSemiColonFromString()
    {
        $contentTypeHeader = ContentDisposition::fromString(
            'Content-Disposition: attachment; filename="test-case.txt";'
        );
        $params = $contentTypeHeader->getParameters();
        $this->assertEquals(['filename' => 'test-case.txt'], $params);
    }

    public static function getLiteralData()
    {
        return [
            [
                ['filename' => 'foo; bar.txt'],
                'attachment; filename="foo; bar.txt"'
            ],
            [
                ['filename' => 'foo&bar.txt'],
                'attachment; filename="foo&bar.txt"'
            ],
            [
                [],
                'inline'
            ],
        ];
    }

    /**
     * @dataProvider getLiteralData
     */
    public function testHandlesLiterals($expected, $header)
    {
        $header = ContentDisposition::fromString('Content-Disposition: ' . $header);
        $this->assertEquals($expected, $header->getParameters());
    }

    /**
     * @dataProvider setDispositionProvider
     */
    public function testFromString($disposition, $parameters, $fieldValue, $expectedToString)
    {
        $header = ContentDisposition::fromString($expectedToString);

        $this->assertInstanceOf(ContentDisposition::class, $header);
        $this->assertEquals('Content-Disposition', $header->getFieldName(), 'getFieldName() value not match');
        $this->assertEquals($disposition, $header->getDisposition(), 'getDisposition() value not match');
        $this->assertEquals($fieldValue, $header->getFieldValue(), 'getFieldValue() value not match');
        $this->assertEquals($parameters, $header->getParameters(), 'getParameters() value not match');
        $this->assertEquals($expectedToString, $header->toString(), 'toString() value not match');
    }

    /**
     * @dataProvider setDispositionProvider
     */
    public function testSetDisposition($disposition, $parameters, $fieldValue, $expectedToString)
    {
        $header = new ContentDisposition();

        $header->setDisposition($disposition);
        foreach ($parameters as $name => $value) {
            $header->setParameter($name, $value);
        }

        $this->assertEquals('Content-Disposition', $header->getFieldName(), 'getFieldName() value not match');
        $this->assertEquals($disposition, $header->getDisposition(), 'getDisposition() value not match');
        $this->assertEquals($fieldValue, $header->getFieldValue(), 'getFieldValue() value not match');
        $this->assertEquals($parameters, $header->getParameters(), 'getParameters() value not match');
        $this->assertEquals($expectedToString, $header->toString(), 'toString() value not match');
    }

    public function testGetSetEncoding()
    {
        $header = new ContentDisposition();

        // default value
        $this->assertEquals('ASCII', $header->getEncoding());

        $header->setEncoding('UTF-8');
        $this->assertEquals('UTF-8', $header->getEncoding());

        $header->setEncoding('ASCII');
        $this->assertEquals('ASCII', $header->getEncoding());
    }

    /**
     * @dataProvider invalidHeaderLinesProvider
     */
    public function testFromStringThrowException($headerLine, $expectedException, $exceptionMessage)
    {
        $this->expectException($expectedException);
        $this->expectExceptionMessage($exceptionMessage);
        ContentDisposition::fromString($headerLine);
    }

    public function testFromStringHandlesContinuations()
    {
        $header = ContentDisposition::fromString("Content-Disposition: attachment;\r\n level=1");
        $this->assertEquals('attachment', $header->getDisposition());
        $this->assertEquals(['level' => '1'], $header->getParameters());
    }

    /**
     * @dataProvider invalidParametersProvider
     */
    public function testSetParameterThrowException($paramName, $paramValue, $expectedException, $exceptionMessage)
    {
        $header = new ContentDisposition();
        $header->setDisposition('attachment');

        $this->expectException($expectedException);
        $this->expectExceptionMessage($exceptionMessage);
        $header->setParameter($paramName, $paramValue);
    }

    /**
     * @dataProvider getParameterProvider
     */
    public function testGetParameter($fromString, $paramName, $paramValue)
    {
        $header = ContentDisposition::fromString($fromString);
        $this->assertEquals($paramValue, $header->getParameter($paramName));
    }

    public function testRemoveParameter()
    {
        $header = ContentDisposition::fromString('Content-Disposition: inline');

        $this->assertEquals(false, $header->removeParameter('no-such-parameter'));

        $header->setParameter('name', 'value');
        $this->assertEquals(true, $header->removeParameter('name'));
    }

    public function setDispositionProvider()
    {
        $foldingFieldValue = "attachment;\r\n filename=\"this-test-filename-is-long-enough-to-flow-to-two-lines.txt\"";
        $foldingHeaderLine = "Content-Disposition: $foldingFieldValue";
        $continuationFieldValue = "attachment;\r\n filename*0=\"this-file-name-is-so-long-that-it-does-not-even\";\r\n filename*1=\"-fit-on-a-whole-line-by-itself-so-we-need-to-sp\";\r\n filename*2=\"lit-it-with-value-continuation.txt\"";
        $continuationHeaderLine = "Content-Disposition: $continuationFieldValue";

        $encodedHeaderLine = 'Content-Disposition: attachment; filename="=?UTF-8?Q?=C3=93?="';
        $encodedFieldValue = 'attachment; filename="Ó"';

        // @codingStandardsIgnoreStart
        return [
            // Description => [$disposition, $parameters, $fieldValue, toString()]
            'inline with no parameters' => ['inline', [], 'inline', 'Content-Disposition: inline'],
            'parameter on one line' => ['inline', ['level' => '1'], 'inline; level="1"'  , 'Content-Disposition: inline; level="1"'],
            'parameter use header folding' => [
                'attachment',
                ['filename' => 'this-test-filename-is-long-enough-to-flow-to-two-lines.txt'],
                $foldingFieldValue,
                $foldingHeaderLine,
            ],
            'encoded characters' => ['attachment', ['filename' => 'Ó'], $encodedFieldValue, $encodedHeaderLine],
            'value continuation' => [
                'attachment',
                ['filename' => 'this-file-name-is-so-long-that-it-does-not-even-fit-on-a-whole-line-by-itself-so-we-need-to-split-it-with-value-continuation.txt'],
                $continuationFieldValue,
                $continuationHeaderLine,
            ],
            'multiple simple parameters' => ['inline', ['one' => 1, 'two' => 2], 'inline; one="1"; two="2"', 'Content-Disposition: inline; one="1"; two="2"']
        ];
        // @codingStandardsIgnoreEnd
    }

    public function invalidParametersProvider()
    {
        $invalidArgumentException = InvalidArgumentException::class;

        // @codingStandardsIgnoreStart
        return [
            // Description => [param name, param value, expected exception, exception message contain]
            'invalid name' => ["b\r\na\rr\n", 'baz', $invalidArgumentException, 'parameter name'],
            'name too long' => ['this-parameter-name-is-so-long-that-it-leaves-no-room-for-any-value-to-be-set', 'too long', $invalidArgumentException, 'too long'],
        ];
        // @codingStandardsIgnoreEnd
    }

    public function invalidHeaderLinesProvider()
    {
        $invalidArgumentException = InvalidArgumentException::class;

        // @codingStandardsIgnoreStart
        return [
            // Description => [header line, expected exception, exception message contain]
            'invalid name' => ['Content-Disposition' . chr(32) . ': inline', $invalidArgumentException, 'header name'],
            'newline' => ["Content-Disposition: inline;\nlevel=1", $invalidArgumentException, 'header value'],
            'cr-lf' => ["Content-Disposition: inline\r\n;level=1", $invalidArgumentException, 'header value'],
            'multiline' => ["Content-Disposition: inline;\r\nlevel=1\r\nq=0.1", $invalidArgumentException, 'header value'],
            'incomplete sequence' => ["Content-Disposition: attachment;\r\n filename*0=\"first-part\";\r\n filename*2=\"third-part\"", $invalidArgumentException, 'incomplete continuation']
        ];
        // @codingStandardsIgnoreEnd
    }

    public function getParameterProvider()
    {
        // @codingStandardsIgnoreStart
        return [
            // Description => [from string, parameter name, parameter Value]
            'no such parameter' => ['Content-Disposition: inline', 'no-such-parameter', null],
            'filename' => ['Content-Disposition: attachment; filename="success.txt"', 'filename', 'success.txt'],
            'continued-value' => [
                "Content-Disposition: attachment;\r\n filename*0=\"this-file-name-is-so-long-that-it-does-not-even\";\r\n filename*1=\"-fit-on-a-whole-line-by-itself-so-we-need-to-sp\";\r\n filename*2=\"lit-it-with-value-continuation.txt\"",
                'filename',
                'this-file-name-is-so-long-that-it-does-not-even-fit-on-a-whole-line-by-itself-so-we-need-to-split-it-with-value-continuation.txt',
            ]
        ];
        // @codingStandardsIgnoreEnd
    }
}
