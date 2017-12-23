<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Mail\Header;

use PHPUnit\Framework\TestCase;
use Zend\Mail\Header;

class HeaderLoaderTest extends TestCase
{
    /**
     * @var Header\HeaderLoader
     */
    private $headerLoader;

    public function setUp()
    {
        $this->headerLoader = new Header\HeaderLoader();
    }

    /**
     * @param $name
     * @param $expected
     * @param $default
     * @dataProvider provideHeaderNames
     */
    public function testHeaderIsProperlyLoaded($name, $expected, $default = null)
    {
        $this->assertEquals($expected, $this->headerLoader->get($name, $default));
    }

    public function provideHeaderNames()
    {
        return [
            'with existing name' => ['to', Header\To::class],
            'with non-existent name' => ['foo', null],
            'with default value' => ['foo', Header\GenericHeader::class, Header\GenericHeader::class],
        ];
    }

    public function testHeaderExistenceIsProperlyChecked()
    {
        $this->assertTrue($this->headerLoader->has('to'));
        $this->assertTrue($this->headerLoader->has('To'));
        $this->assertTrue($this->headerLoader->has('Reply_to'));
        $this->assertTrue($this->headerLoader->has('SUBJECT'));
        $this->assertFalse($this->headerLoader->has('foo'));
        $this->assertFalse($this->headerLoader->has('bar'));
    }

    public function testHeaderCanBeAdded()
    {
        $this->assertFalse($this->headerLoader->has('foo'));
        $this->headerLoader->add('foo', Header\GenericHeader::class);
        $this->assertTrue($this->headerLoader->has('foo'));
    }

    public function testHeaderCanBeRemoved()
    {
        $this->assertTrue($this->headerLoader->has('to'));
        $this->headerLoader->remove('to');
        $this->assertFalse($this->headerLoader->has('to'));
    }
}
