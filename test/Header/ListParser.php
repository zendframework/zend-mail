<?php
/**
 * @see       https://github.com/zendframework/zend-mail for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-mail/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Mail\Header;

use PHPUnit\Framework\TestCase;
use Zend\Mail\Header\ListParser;

/**
 * @covers Zend\Mail\Header\ListParser<extended>
 */
class ListParserTest extends TestCase
{
    public function testParseIgnoreQuoteDelimiterIfAlreadyInQuote()
    {
        $parsed = ListParser::parse('"john\'doe" <john@doe.com>,jane <jane@doe.com>');
        $this->assertEquals($parsed, [
            '"john\'doe" <john@doe.com>',
            'jane <jane@doe.com>'
        ]);
    }
}
