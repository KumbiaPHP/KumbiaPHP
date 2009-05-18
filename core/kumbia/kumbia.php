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
 * Esta es la clase principal del framework, contiene metodos importantes
 * para cargar los controladores y ejecutar las acciones en estos ademas
 * de otras funciones importantes
 * 
 * @category   Kumbia
 * @package    Core 
 * @copyright  Copyright (c) 2005-2009 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */
final class Kumbia
{
    /**
     * Almacena la version actual del Framework
     *
     */
    const KUMBIA_VERSION = '1.0';
    /**
     * Almacena datos compartidos en la aplicacion
     *
     * @var array
     */
    static public $data = array();
    /**
     * Cachea la salida al navegador
     *
     * @var string
     */
    static public $content = '';
	/**
	 * Inicia la aplicacion
	 *
	 **/
    public static function init_application() {
		/**
		 * Carga del boot.ini
		 */
		Load::boot();
	
		/**
         * @see Controller
         */
        require CORE_PATH . 'kumbia/controller/application/application.php';
        	
        /**
         * @see ApplicationController
         */
        include_once APP_PATH . 'application.php';
		
		$config = Config::read('config.ini');
        /**
         * Establecer el timezone para las fechas y horas
         */
        if (isset($config['application']['timezone'])) {
            date_default_timezone_set($config['application']['timezone']);
        } else {
            date_default_timezone_set('America/New_York');
        }
		
		/**
		 * Asigna localizacion
		 **/
		if(isset($config['application']['locale']) && $config['application']['locale']) {
			setlocale(LC_ALL, $config['application']['locale']);
		}
		
		/**
        * Establecer el charset de la app en la constante APP_CHARSET
        */
	 	define('APP_CHARSET', strtoupper($config['application']['charset']));
    }
    /**
     * Funci&oacute;n Principal donde se ejecutan los controladores
     *
	 * @params string $url url
     * @return boolean
     */
    public static function main($url) {
		/**
		 * @see Router
		 */
		require CORE_PATH . 'kumbia/router/router.php';
		/**
		 * El Router analiza la url
		 **/
		Router::rewrite($url);
		
    	/**
         * @see Dispatcher
         */
        require CORE_PATH . 'kumbia/dispatcher/dispatcher.php';
        /**
         * @see Flash
         */
        require CORE_PATH . 'extensions/messages/flash.php';
        /**
         * @see Utils
         */
        require CORE_PATH . 'kumbia/util/utils.php';
        /**
         * @see Util
         */
        require CORE_PATH . 'kumbia/util/util.php';
		/**
         * @see Load
         */
        require CORE_PATH . 'kumbia/load.php';
		
		/**
		 * Kumbia reinicia las variables de aplicación cuando cambiamos
		 * entre una aplicación y otra. Init Application define KUMBIA_PATH
		 */
		self::init_application();
	
		/**
		 * Iniciar el buffer de salida
		 */
		ob_start();

		$application = Config::get('config.application');
		
		/**
		 * Carga los modelos del directorio models y las clases necesarias
		 */
		if($application['database']){
			 /**
			 * @see Db
			 */
			require CORE_PATH . 'extensions/db/db.php';
			/**
			 * @see ActiveRecordBase
			 */
			require CORE_PATH . 'extensions/db/active_record_base/active_record_base.php';
			/**
			 * El driver de Kumbia es cargado segun lo que diga en config.ini
			 */
			if (!DbLoader::load_driver()) {
				return false;
			}
			/**
			 * Inicializa los Modelos. model_base es el modelo base
			 */
			include_once APP_PATH . 'model_base.php';
		}
		
		/**
		 * Ciclo del enrutador
		 */
		$controller = Dispatcher::execute();
		while (Router::get_routed()) {
			Router::set_routed(false);
			$controller = Dispatcher::execute();
		}
		
		$view = self::_get_view($controller, $url);
		ob_end_clean();
		echo $view;
    }
    /**
     * Imprime los CSS cargados mediante stylesheet_link_tag
     *
     */
    public static function stylesheet_link_tags() {
        $imports = self::$data['KUMBIA_CSS_IMPORTS'];
        if ($imports && is_array($imports)) {
            foreach ($imports as $css) {
                echo $css;
            }
        } else {
            echo $imports;
        }
    }
	/**
	 * Obtiene la vista
	 *
	 * @param string $views_dir directorio de vistas
	 * @param object $controller controlador
	 * @param string $url url
	 * @return string
	 **/
	protected static function _get_view($controller, $url)
	{
        /**
         * @see Tags
         */
        require_once CORE_PATH . 'helpers/tags.php';
        
        /**
         * Mapea los atributos del controller en el scope
         *
         **/
        extract(get_object_vars($controller));
		
		/**
		 * Intenta cargar la vista desde la cache
		 **/
		self::$content = $cache['type']=='view' ? Cache::get($url, 'kumbia.views') : '';
		if(!self::$content) {
			/**
			 * Carga el el contenido del buffer de salida
			 *
			 **/
			self::$content = ob_get_contents();
			ob_clean();
				
			if ($module_name){
				$controller_views_dir =  APP_PATH . "views/$module_name/$controller_name";
			} else {
				$controller_views_dir =  APP_PATH . "views/$controller_name";
			}
                
			/**
			 * Renderizar vista
			 *
			 **/
			if($controller->view) {
				ob_start();
				include "$controller_views_dir/$view.phtml";
				self::$content = ob_get_clean();
			}
			
			if($cache['type'] == 'view') {
				Cache::save(self::$content, $cache['time'], $url, 'kumbia.views');
			}
		}
	
		/**
		 * Verifica si se debe renderizar solo la vista
		 *
		 **/
		if($controller->response == 'view' || $controller->response == 'xml') {
			return self::$content;
		}
		
		/**
		 * Renderizar template
		 *
		 **/
		if($controller->template) {
			$template = APP_PATH . "views/templates/$controller->template.phtml";
		} else {
			$template = APP_PATH . "views/templates/$controller_name.phtml";
		}
		
		if(file_exists($template)) {
			ob_start();
			include $template;
			self::$content = ob_get_clean();
				
			if($cache['type'] == 'template') {
				Cache::save(self::$content, $cache['time'], $url, 'kumbia.templates');
			}
		}
		
		return self::$content;
	}
}