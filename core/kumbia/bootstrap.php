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
 * @category   Kumbia
 * @package    Core
 * @copyright  Copyright (c) 2005 - 2016 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

/**
 * Este script ejecuta la carga de KumbiaPHP
 *
 * @category   Kumbia
 * @package    Core
 */

// Iniciar el buffer de salida
ob_start();

// Versión de KumbiaPHP
function kumbia_version() {
    return 'RC 1.0';
}

// @see KumbiaException
function handle_exception($e) {
    KumbiaException::handleException($e);
}

// Inicializar el ExceptionHandler
set_exception_handler('handle_exception');

// @see Autoload
require CORE_PATH . 'kumbia/autoload.php';

// @see Config
require_once CORE_PATH . 'kumbia/config.php';

// Lee la configuracion
$config = Config::read('config');

// Carga la cache y verifica si esta cacheado el template, al estar en produccion
if (PRODUCTION) {
    // @see Cache
    require CORE_PATH . 'libs/cache/cache.php';

    //Asigna el driver por defecto usando el config.ini
    if (isset($config['application']['cache_driver'])) {
        Cache::setDefault($config['application']['cache_driver']);
    }

    // Verifica si esta cacheado el template
    if ($template = Cache::driver()->get($url, 'kumbia.templates')) {
        //verifica cache de template para la url
        echo $template;
        echo '<!-- Tiempo: ' . round(microtime(TRUE) - START_TIME, 5) . ' seg. -->';
        exit(0);
    }
}

// @see Router
require CORE_PATH . 'kumbia/router.php';

// @see Controller
require APP_PATH . 'libs/app_controller.php';

// @see KumbiaView
require APP_PATH . 'libs/view.php';

// Ejecuta el request
// Dispatch y renderiza la vista
View::render(Router::execute($url));

// Fin del request
//exit();
