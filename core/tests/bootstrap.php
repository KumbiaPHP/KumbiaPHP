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
 * @package    Core
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

defined('CORE_PATH') || define('CORE_PATH', dirname(__DIR__) . '/');
defined('APP_PATH') || define('APP_PATH', __DIR__ . '/');
defined('PUBLIC_PATH') || define('PUBLIC_PATH', 'http://127.0.0.1/');

require_once CORE_PATH.'kumbia/autoload.php';
require_once __DIR__.'/../../vendor/autoload.php';

spl_autoload_register('kumbia_autoload_helper', true, true);
