<?php
/**
 * KumbiaPHP web & app Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://wiki.kumbiaphp.com/Licencia
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@kumbiaphp.com so we can send you a copy immediately.
 *
 * Script para consolas de KumbiaPHP
 *
 * @category   Kumbia
 * @package    Console
 * @copyright  Copyright (c) 2005-2014 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */
// Define el CORE_PATH
define('CORE_PATH', dirname(dirname(__FILE__)) . '/');

/**
 * @see Console
 */
require CORE_PATH . 'kumbia/console.php';

// Ejecuta el despachador
Console::dispatch($argv);