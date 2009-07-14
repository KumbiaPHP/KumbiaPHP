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
 * Esta es la clase para arrancar el framework y la aplicacion
 * 
 * @category   Kumbia
 * @package    Core 
 * @copyright  Copyright (c) 2005-2009 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */
final class Bootstrap
{
    /**
     * Arranca la aplicacion
     *
     * @param string $url ruta a acceder
     **/
    public static function boot($url)
    {
        /**
         * @see KumbiaException
         */
        require CORE_PATH . 'kumbia/kumbia_exception.php';
        /**
         * Inicializar el ExceptionHandler
         */
        set_exception_handler(array('KumbiaException', 'handle_exception'));

        /**
         * @see Config
         */
        require CORE_PATH . 'kumbia/config.php';
        /**
         * Lee la configuracion
         */
        $config = Config::read('config.ini');
        
        /**
         * @see Cache
         **/
        require CORE_PATH . 'libraries/cache/cache.php';
        /**
         * Asigna el driver para cache
         **/
        if(isset($config['application']['cache_driver'])) {
            Cache::set_driver($config['application']['cache_driver']);
        }

        /**
         * Desactiva la cache
         **/
        if(!$config['application']['production']) {
            Cache::active(false);
        } elseif ($template = Cache::get($url, 'kumbia.templates')) { //verifica cache de template para la url
            echo $template;
            echo '<!-- Tiempo: '.round(microtime(1)-START_TIME,4).' seg. -->';
            exit(0);
        }
        
		/**
		 * Asignando locale
		 **/
		if(isset($config['application']['locale'])) {
			setlocale(LC_ALL, $config['application']['locale']);
		}
		
        /**
         * Establecer el timezone para las fechas y horas
         */
        if (isset($config['application']['timezone'])) {
            date_default_timezone_set($config['application']['timezone']);
        }
		
        /**
         * Establecer el charset de la app en la constante APP_CHARSET
         */
        if(isset($config['application']['charset'])) {
            define('APP_CHARSET', strtoupper($config['application']['charset']));
        } else {
            define('APP_CHARSET', 'UTF-8');
        }

		/**
         * @see Load
         */
        require CORE_PATH . 'kumbia/load.php';
		/**
		 * Carga del boot.ini
		 */
		Load::boot();

        /**
         * @see Kumbia
         */
        require CORE_PATH . 'kumbia/kumbia.php';
        /**
         * Atender la petición
         */
        Kumbia::main($url);
    }
}