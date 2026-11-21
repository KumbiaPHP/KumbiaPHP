<?php
/**
 * KumbiaPHP web & app Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 *
 * @copyright  Copyright (c) 2005 - 2026 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Helper function to access private/protected properties of an object.
 *
 * @param object $obj The object instance.
 * @param string $name The name of the property.
 * @return mixed The value of the property.
 */
function _getProperty(object $obj, string $name): mixed {
    $property = (new ReflectionClass($obj))->getProperty($name);
    return $property->getValue($obj);
}

/**
 * Helper function to access private/protected methods of an object.
 *
 * @param object $obj The object instance.
 * @param string $name The name of the method.
 * @return mixed The result of invoking the method.
 */
function _getMethod(object $obj, string $name): mixed {
    $method = (new ReflectionClass($obj))->getMethod($name);
    return $method->invoke($obj);
}
