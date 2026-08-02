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
 * @package    Registry
 *
 * @copyright  Copyright (c) 2005 - 2023 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * @category    Test
 * @package     Registry
 */
class RegistryTest extends PHPUnit\Framework\TestCase
{
    public function testSetAndGetScalarValue()
    {
        Registry::set('registry_scalar', 'value');

        $this->assertSame('value', Registry::get('registry_scalar'));
    }

    public function testSetAndGetArrayValue()
    {
        $value = ['first', 'second'];

        Registry::set('registry_array', $value);

        $this->assertSame($value, Registry::get('registry_array'));
    }

    public function testSetAndGetObjectValue()
    {
        $value = new stdClass();

        Registry::set('registry_object', $value);

        $this->assertSame($value, Registry::get('registry_object'));
    }

    public function testAppendAndPrependPreserveNull()
    {
        Registry::set('registry_null_append', null);
        $this->assertNull(Registry::get('registry_null_append'));
        Registry::append('registry_null_append', 'after');

        Registry::set('registry_null_prepend', null);
        $this->assertNull(Registry::get('registry_null_prepend'));
        Registry::prepend('registry_null_prepend', 'before');

        $this->assertSame([null, 'after'], Registry::get('registry_null_append'));
        $this->assertSame(['before', null], Registry::get('registry_null_prepend'));
    }

    public function testAppendAndPrependWrapScalarValues()
    {
        Registry::set('registry_scalar_append', 'first');
        Registry::append('registry_scalar_append', 'second');

        Registry::set('registry_scalar_prepend', 'second');
        Registry::prepend('registry_scalar_prepend', 'first');

        $this->assertSame(['first', 'second'], Registry::get('registry_scalar_append'));
        $this->assertSame(['first', 'second'], Registry::get('registry_scalar_prepend'));
    }

    public function testAppendAndPrependKeepArraysFlat()
    {
        Registry::set('registry_array_append', ['first']);
        Registry::append('registry_array_append', 'second');

        Registry::set('registry_array_prepend', ['second']);
        Registry::prepend('registry_array_prepend', 'first');

        $this->assertSame(['first', 'second'], Registry::get('registry_array_append'));
        $this->assertSame(['first', 'second'], Registry::get('registry_array_prepend'));
    }

    public function testAppendAndPrependCreateMissingKeys()
    {
        Registry::append('registry_missing_append', 'value');
        Registry::prepend('registry_missing_prepend', 'value');

        $this->assertSame(['value'], Registry::get('registry_missing_append'));
        $this->assertSame(['value'], Registry::get('registry_missing_prepend'));
    }

    public function testRepeatedSetReplacesValueExactly()
    {
        Registry::set('registry_replacement', ['old']);
        Registry::set('registry_replacement', false);

        $this->assertFalse(Registry::get('registry_replacement'));
    }
}
