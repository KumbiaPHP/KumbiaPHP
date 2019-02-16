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
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Script para consolas de KumbiaPHP
 *
 * @category   Kumbia
 * @package    Console
 */
// Define el CORE_PATH
define('CORE_PATH', dirname(__DIR__) . '/');

/**
 * @see Console
 */
require CORE_PATH . 'kumbia/console.php';

// Ejecuta el despachador
Console::dispatch($argv);