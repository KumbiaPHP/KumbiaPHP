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
 * @package    Core
 *
 * @copyright  Copyright (c) 2005 - 2021 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Este script ejecuta la carga de KumbiaPHP con Workerman
 *
 * @category   Kumbia
 * @package    Core
 */


require_once CORE_PATH.'../../autoload.php';

use Workerman\Protocols\Http;
use Workerman\Lib\Timer;

// Iniciar el buffer de salida
//ob_start();

// Kumbia Version
require CORE_PATH.'kumbia/kumbia_version.php';

/**
 * Inicializar el ExceptionHandler TODO
 * @see KumbiaException
 *
 * @return void
 */
// set_exception_handler(function($e) {
//     KumbiaException::handleException($e);
// });

// @see Autoload
require CORE_PATH.'kumbia/autoload.php';
// @see Config
require CORE_PATH.'kumbia/config.php';

// @see Router
require CORE_PATH.'kumbia/router.php';
require CORE_PATH.'kumbia/static_router.php';
// @see Controller
require APP_PATH.'libs/app_controller.php';
// @see KumbiaView
require APP_PATH.'libs/view.php';
// Ejecuta el request
// Dispatch y renderiza la vista

function kumbiaSend() {
    ob_start();ob_start();
    View::render(StaticRouter::execute($_SERVER['REQUEST_URI']));
    Http::header(WorkerTimer::$date);
    if (ob_get_level() > 1) {
        ob_end_flush();
    }
    return ob_get_clean();
}

class WorkerTimer
{
    public static $date;

    public static function init()
    {
        self::$date = 'Date: '.gmdate('D, d M Y H:i:s').' GMT';
        Timer::add(1, function() {
            WorkerTimer::$date = 'Date: '.gmdate('D, d M Y H:i:s').' GMT';
        });
    }
}

function kumbiaInit() {
    WorkerTimer::init();
}
