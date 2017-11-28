<?php

namespace Engelsystem\Test\Unit\Config;

use Engelsystem\Config\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @covers \Engelsystem\Config\Config::get
     */
    public function testGet()
    {
        $config = new Config();

        $config->set('test', 'FooBar');
        $this->assertEquals(['test' => 'FooBar'], $config->get(null));
        $this->assertEquals('FooBar', $config->get('test'));

        $this->assertEquals('defaultValue', $config->get('notExisting', 'defaultValue'));

        $this->assertNull($config->get('notExisting'));
    }

    /**
     * @covers \Engelsystem\Config\Config::set
     */
    public function testSet()
    {
        $config = new Config();

        $config->set('test', 'FooBar');
        $this->assertEquals('FooBar', $config->get('test'));

        $config->set([
            'name' => 'Engelsystem',
            'mail' => ['user' => 'test'],
        ]);
        $this->assertEquals('Engelsystem', $config->get('name'));
        $this->assertEquals(['user' => 'test'], $config->get('mail'));
    }

    /**
     * @covers \Engelsystem\Config\Config::has
     */
    public function testHas()
    {
        $config = new Config();

        $this->assertFalse($config->has('test'));

        $config->set('test', 'FooBar');
        $this->assertTrue($config->has('test'));
    }

    /**
     * @covers \Engelsystem\Config\Config::remove
     */
    public function testRemove()
    {
        $config = new Config();
        $config->set(['foo' => 'bar', 'test' => '123']);

        $config->remove('foo');
        $this->assertEquals(['test' => '123'], $config->get(null));
    }

    /**
     * @covers \Engelsystem\Config\Config::__get
     */
    public function testMagicGet()
    {
        $config = new Config();

        $config->set('test', 'FooBar');
        $this->assertEquals('FooBar', $config->test);
    }

    /**
     * @covers \Engelsystem\Config\Config::__set
     */
    public function testMagicSet()
    {
        $config = new Config();

        $config->test = 'FooBar';
        $this->assertEquals('FooBar', $config->get('test'));
    }

    /**
     * @covers \Engelsystem\Config\Config::__isset
     */
    public function testMagicIsset()
    {
        $config = new Config();

        $this->assertFalse(isset($config->test));

        $config->set('test', 'FooBar');
        $this->assertTrue(isset($config->test));
    }

    /**
     * @covers \Engelsystem\Config\Config::__unset
     */
    public function testMagicUnset()
    {
        $config = new Config();
        $config->set(['foo' => 'bar', 'test' => '123']);

        unset($config->foo);
        $this->assertEquals(['test' => '123'], $config->get(null));
    }
}
