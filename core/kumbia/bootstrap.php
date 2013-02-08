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
 * @copyright  Copyright (c) 2005-2012 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

/**
 * Este script ejecuta la carga de KumbiaPHP
 *
 * @category   Kumbia
 * @package    Core
 */

// Inicia la sesion
session_start();

// Iniciar el buffer de salida
ob_start();

// Versión de KumbiaPHP
function kumbia_version()
{
    return '1.0 Beta 2';
}

// @see KumbiaException
function handle_exception($e)
{
    KumbiaException::handle_exception($e);
}

// Registrar la autocarga
spl_autoload_register('auto');

// Inicializar el ExceptionHandler
set_exception_handler('handle_exception');

// @see Util
require CORE_PATH . 'kumbia/util.php';

// @see Config
require CORE_PATH . 'kumbia/config.php';

// Lee la configuracion
$config = Config::read('config');

// Constante que indica si la aplicacion se encuentra en produccion
if (!defined('PRODUCTION')) {
    define('PRODUCTION', $config['application']['production']);
}

// Carga la cache y verifica si esta cacheado el template, al estar en produccion
if (PRODUCTION) {
    // @see Cache
    require CORE_PATH . 'libs/cache/cache.php';

    //Asigna el driver por defecto usando el config.ini
    if (isset($config['application']['cache_driver']))
        Cache::setDefault($config['application']['cache_driver']);

    // Verifica si esta cacheado el template
    if ($template = Cache::driver()->get($url, 'kumbia.templates')) { //verifica cache de template para la url
        echo $template;
        echo '<!-- Tiempo: ' . round(microtime(1) - START_TIME, 4) . ' seg. -->';
        exit(0);
    }
}

// Asignando locale
if (isset($config['application']['locale'])) {
    setlocale(LC_ALL, $config['application']['locale']);
}

// Establecer el timezone para las fechas y horas
if (isset($config['application']['timezone'])) {
    ini_set('date.timezone', $config['application']['timezone']);
}

// Establecer el charset de la app en la constante APP_CHARSET
if (isset($config['application']['charset'])) {
    define('APP_CHARSET', strtoupper($config['application']['charset']));
} else {
    define('APP_CHARSET', 'UTF-8');
}

// Autocarga de clases
function auto($class)
{

    // Optimizando carga
    if ($class == 'ActiveRecord') {
        return include APP_PATH . 'libs/active_record.php';
    }
    if ($class == 'Load') {
        return include CORE_PATH . 'kumbia/load.php';
    }   

    // Pasando a smallcase
    $class = Util::smallcase($class);
    if (is_file(APP_PATH . "extensions/helpers/$class.php")) {
        return include APP_PATH . "extensions/helpers/$class.php";
    }
    if (is_file(CORE_PATH . "extensions/helpers/$class.php")) {
        return include CORE_PATH . "extensions/helpers/$class.php";
    }
    if (is_file(APP_PATH . "libs/$class.php")) {
        return include APP_PATH . "libs/$class.php";
    }
    if (is_file(CORE_PATH . "libs/$class/$class.php")) {
        return include CORE_PATH . "libs/$class/$class.php";
    }

    if ($class == 'kumbia_exception') {
        include CORE_PATH . 'kumbia/kumbia_exception.php';
    }
}

// @see Router
require CORE_PATH . 'kumbia/router.php';

// @see Controller
require APP_PATH . 'libs/app_controller.php';

// @see KumbiaView
require APP_PATH . 'libs/view.php';

// Ejecuta el request
try {
    // Dispatch y renderiza la vista
    View::render(Router::execute($url), $url);
} catch (KumbiaException $e) {
    KumbiaException::handle_exception($e);
}

// Fin del request
exit();
