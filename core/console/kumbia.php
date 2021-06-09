<?php
/**
 * KumbiaPHP web & app Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @category   Kumbia
 * @package    Console
 *
 * @copyright  Copyright (c) 2005 - 2020 KumbiaPHP Team (https://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * KumbiaPHP Console Script
 *
 * @category   Kumbia
 * @package    Console
 */
// Define the CORE_PATH
define('CORE_PATH', dirname(__DIR__) . '/');

/**
 * @see Console
 */
require CORE_PATH . 'kumbia/console.php';

// Run the dispatcher
Console::dispatch($argv);