<?php
/**
/**
 * KumbiaPHP web & app Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @category   Kumbia Tests
 * @package    Core
 *
 * @copyright  Copyright (c) 2005 - 2023 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
//ob_start();

require_once 'KumbiaTestTrait.php';

//default in any server
http_response_code(200);

defined('PRODUCTION') || define('PRODUCTION', false);
defined('APP_CHARSET') || define('APP_CHARSET', 'utf-8');

defined('CORE_PATH') || define('CORE_PATH', dirname(dirname(dirname(__DIR__))) . '/core/');
defined('APP_PATH') || define('APP_PATH', dirname(__DIR__) . '/');
defined('PUBLIC_PATH') || define('PUBLIC_PATH', 'http://127.0.0.1/');

// Kumbia Version
require CORE_PATH.'kumbia/kumbia_version.php';
require_once CORE_PATH.'kumbia/autoload.php';
require_once APP_PATH.'../../vendor/autoload.php';

require CORE_PATH . 'kumbia/config.php';
require CORE_PATH . 'kumbia/router.php';
//spl_autoload_register('kumbia_autoload_helper', true, true);

//function handle_exception($e) {
//    KumbiaException::handleException($e);
//}
// Inicializar el ExceptionHandler
//set_exception_handler('handle_exception');