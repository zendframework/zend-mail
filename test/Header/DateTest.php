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
 * @covers Zend\Mail\Header\Date<extended>
 */
class DateTest extends TestCase
{
    public function headerLines()
    {
        return [
            'newline'      => ["Date: xxx yyy\n"],
            'cr-lf'        => ["Date: xxx yyy\r\n"],
            'cr-lf-wsp'    => ["Date: xxx yyy\r\n\r\n"],
            'multiline'    => ["Date: xxx\r\ny\r\nyy"],
        ];
    }

    /**
     * @dataProvider headerLines
     * @group ZF2015-04
     */
    public function testFromStringRaisesExceptionOnCrlfInjectionAttempt($header)
    {
        $this->expectException(Header\Exception\InvalidArgumentException::class);
        Header\Date::fromString($header);
    }

    /**
     * @group ZF2015-04
     */
    public function testPreventsCRLFInjectionViaConstructor()
    {
        $this->expectException(Header\Exception\InvalidArgumentException::class);
        $address = new Header\Date("This\ris\r\na\nCRLF Attack");
    }
}
