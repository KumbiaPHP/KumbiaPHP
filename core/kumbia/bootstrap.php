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
 * @copyright  Copyright (c) 2005 - 2020 KumbiaPHP Team (https://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * The following script executes the loading of KumbiaPHP
 *
 * -
 * Este script ejecuta la carga de KumbiaPHP
 * 
 * @category   Kumbia
 * @package    Core
 */

// Iniciar el buffer de salida
ob_start();

/**
 * KumbiaPHP Version
 *
 * @var string
 */
const KUMBIA_VERSION = '2.0.0';

/**
 * Initiates the ExceptionHandler
 * 
 * =
 * 
 * Inicializar el ExceptionHandler
 * @see KumbiaException
 *
 * @return void
 */
set_exception_handler(function($e) {
    KumbiaException::handleException($e);
});


// @see Autoload
require CORE_PATH.'kumbia/autoload.php';

// @see Config
require CORE_PATH.'kumbia/config.php';

if (PRODUCTION && Config::get('config.application.cache_template')) {
    // @see Cache
    require CORE_PATH.'libs/cache/cache.php';

    // Asigns the default driver from config.ini
    // -
    ///Asigna el driver por defecto usando el config.ini
    if ($config = Config::get('config.application.cache_driver')) {
        Cache::setDefault($config);
    }

    // Checks the template is cached
    // -
    // Verifica si esta cacheado el template
    if ($template = Cache::driver()->get($url, 'kumbia.templates')) {
        //verifica cache de template para la url
        echo $template;
        echo '<!-- Time: ', round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 4), ' ms -->';
        return;
    }
}

// @see Router
require CORE_PATH.'kumbia/router.php';

// @see Controller
require APP_PATH.'libs/app_controller.php';

// @see KumbiaView
require APP_PATH.'libs/view.php';

// Executes the request
// Dispatches and renders the view
// - 
// Ejecuta el request
// Dispatch y renderiza la vista
View::render(Router::execute($url));

// Fin del request exit()
