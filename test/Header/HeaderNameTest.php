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
use Zend\Mail\Header\HeaderName;

/**
 * @covers Zend\Mail\Header\HeaderName<extended>
 */
class HeaderNameTest extends TestCase
{
    /**
     * Data for filter name
     */
    public function getFilterNames()
    {
        return [
            ['Subject', 'Subject'],
            ['Subject:', 'Subject'],
            [':Subject:', 'Subject'],
            ['Subject' . chr(32), 'Subject'],
            ['Subject' . chr(33), 'Subject' . chr(33)],
            ['Subject' . chr(126), 'Subject' . chr(126)],
            ['Subject' . chr(127), 'Subject'],
        ];
    }

    /**
     * @dataProvider getFilterNames
     * @group ZF2015-04
     */
    public function testFilterName($name, $expected)
    {
        $this->assertEquals($expected, HeaderName::filter($name));
    }

    public function validateNames()
    {
        return [
            ['Subject', 'assertTrue'],
            ['Subject:', 'assertFalse'],
            [':Subject:', 'assertFalse'],
            ['Subject' . chr(32), 'assertFalse'],
            ['Subject' . chr(33), 'assertTrue'],
            ['Subject' . chr(126), 'assertTrue'],
            ['Subject' . chr(127), 'assertFalse'],
        ];
    }

    /**
     * @dataProvider validateNames
     * @group ZF2015-04
     */
    public function testValidateName($name, $assertion)
    {
        $this->{$assertion}(HeaderName::isValid($name));
    }

    public function assertNames()
    {
        return [
            ['Subject:'],
            [':Subject:'],
            ['Subject' . chr(32)],
            ['Subject' . chr(127)],
        ];
    }

    /**
     * @dataProvider assertNames
     * @group ZF2015-04
     */
    public function testAssertValidRaisesExceptionForInvalidNames($name)
    {
        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage('Invalid');
        HeaderName::assertValid($name);
    }
}
