<?php
/**
 * KumbiaPHP web & app Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @category   Test
 * @package    Session
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * @category    Test
 * @package     Session
 */
class SessionTest extends PHPUnit\Framework\TestCase
{
    public function setUp()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
    }

    public function testAssertKeyNotExists()
    {
        $this->assertFalse(Session::has('test_key'));
        $this->assertFalse(Session::has('test_key', 'other_namespace'));
    }

    public function testAssertSetAndGet()
    {
        $this->assertFalse(Session::has('test_key'));

        Session::set('test_key', 'value');

        $this->assertTrue(Session::has('test_key'));
        $this->assertSame('value', Session::get('test_key'));
    }

    public function testGetDefaultValue()
    {
        Session::delete('test_key');
        
        $this->assertFalse(Session::has('test_key'));
        $this->assertNull(Session::get('test_key'));
    }

    public function testHasWithNamespaces()
    {
        $this->assertFalse(Session::has('test_key'));
        $this->assertFalse(Session::has('test_key', 'other'));

        Session::set('test_key', 'value');

        $this->assertTrue(Session::has('test_key'));
        $this->assertFalse(Session::has('test_key', 'other'));

        Session::delete('test_key');
        Session::set('test_key', 'other_value', 'other');

        $this->assertFalse(Session::has('test_key'));
        $this->assertTrue(Session::has('test_key', 'other'));
    }

    public function testGetWithNamespaces()
    {
        Session::delete('test_key');
        Session::delete('test_key', 'other');
        
        $this->assertNull(Session::get('test_key'));
        $this->assertNull(Session::get('test_key', 'other'));

        Session::set('test_key', 'value');
        Session::set('test_key', 'other_value', 'other');

        $this->assertSame('value', Session::get('test_key'));
        $this->assertSame('other_value', Session::get('test_key', 'other'));
    }

    public function testDelete()
    {
        Session::set('test_key', 'value');

        $this->assertTrue(Session::has('test_key'));
        $this->assertSame('value', Session::get('test_key'));

        Session::delete('test_key');

        $this->assertFalse(Session::has('test_key'));
        $this->assertNull(Session::get('test_key'));
    }

    public function testDeleteWithNamespace()
    {
        Session::set('test_key', 'value');
        Session::set('test_key', 'other_value', 'other');
        Session::set('test_key', 'another_value', 'another');

        Session::delete('test_key');

        $this->assertFalse(Session::has('test_key'));
        $this->assertTrue(Session::has('test_key', 'other'));
        $this->assertTrue(Session::has('test_key', 'another'));

        Session::delete('test_key', 'other');

        $this->assertFalse(Session::has('test_key', 'other'));
        $this->assertTrue(Session::has('test_key', 'another'));
    }

    protected function tearDown()
    {
        if (session_status() != PHP_SESSION_NONE) {
            @session_unset();
            @session_destroy();
        }
    }
}
