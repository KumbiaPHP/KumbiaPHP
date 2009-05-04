<?php
/**
 * Kumbia PHP Framework
 * PHP version 5
 * LICENSE
 *
 * This source file is subject to the GNU/GPL that is bundled
 * with this package in the file docs/LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.kumbiaphp.com/
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kumbia@kumbiaphp.com so we can send you a copy immediately.
 *
 * @category  Kumbia
 * @package   Kumbia
 *
 * @author    Andres Felipe Gutierrez <andresfelipe@vagoogle.net>
 * @copyright 2008-2008 Emilio Rafael Silveira Tovar <emilio.rst at gmail.com>
 * @copyright 2007-2009 Deivinson Jose Tejeda Brito <deivinsontejeda at gmail.com>
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt GNU/GPL
 * @version   SVN:$id
 * @see       Object
 */

/**
 * Esta es la clase principal del framework, contiene metodos importantes
 * para cargar los controladores y ejecutar las acciones en estos ademas
 * de otras funciones importantes
 *
 * @category  Kumbia
 * @package   Kumbia
 * @author    Andres Felipe Gutierrez <andresfelipe@vagoogle.net>
 * @copyright 2005-2007 Andres Felipe Gutierrez (andresfelipe at vagoogle.net)
 * @copyright 2007-2009 Deivinson Jose Tejeda Brito(deivinsontejeda at gmail.com)
 * @copyright 2008-2008 Emilio Rafael Silveira Tovar (emilio.rst at gmail.com)
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt GNU/GPL
 */
class Kumbia
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
     * Lista de todos los modelos disponibles en la aplicacion
     * Es creado por el _init_models
     * @var array
     */
    static public $models = array();
	/**
	 * Inicia la aplicacion
	 *
	 **/
    static public function init_application() {
		/**
         * @see Controller
         */
        require CORE_PATH . 'kumbia/controller/application/application.php';
        	
        /**
         * @see ApplicationController
         */
        include_once APP_PATH . 'application.php';

        /**
         * La variable kumbia en el apartado modules en config/boot.ini
         * tiene valores estilo logger... esto hace que Kumbia cargue
         * automaticamente en el directorio library/kumbia/logger/logger.php.
         *
         * Esta variable tambien puede ser utilizada para cargar modulos de
         * usuario y clases personalizadas, en la variable extensions.
         *
         */
		$boot = Config::read('boot.ini');
		if($boot['modules']['vendors']){
			$extensions = explode(',', str_replace(' ', '', $boot['modules']['vendors']));
			foreach ($extensions as $extension){
				require_once VENDORS_PATH . "$extension" .'/'.$extension.'.php';
			}
			unset($extensions);
		}
		if($boot['modules']['extensions']){
			$extensions = explode(',', str_replace(' ', '', $boot['modules']['extensions']));
			foreach ($extensions as $extension){
				require_once CORE_PATH . "extensions/$extension" .'/'.$extension.'.php';
			}
			unset($extensions);
		}
		
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
    static function main($url) {
		/**
		 * @see Router
		 */
		require CORE_PATH . 'kumbia/router/router.php';
		/**
		 * El Router analiza la url
		 **/
		Router::rewrite($url);
		
		$application = Config::get('config.application');
		
		/**
		 * Asigna localizacion
		 **/
		if(isset($application['locale'])) {
			setlocale(LC_ALL, $application['locale']);
		}
				
		/**
		 * Kumbia reinicia las variables de aplicación cuando cambiamos
		 * entre una aplicación y otra. Init Application define KUMBIA_PATH
		 */
		self::init_application();

		/**
    	 * Incluyendo las librerias del Framework
    	 */
    	self::_load_library_kumbia();
	
		/**
		 * Iniciar el buffer de salida
		 */
		ob_start();

		/**
		 * Carga los modelos del directorio models y las clases necesarias
		 */
		if(isset($application['database']) && $application['database']){
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
			
			if($application['models_autoload']) {
				self::_init_models();
			}
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
     * Inicializa los modelos en el directorio models
     *
     * @param string $models_dir
     */
    private static function _init_models() {
        /**
         * Path de los modelos incluidos
         */
        if ($models = Cache::get('models', 'kumbia')) {
			$models = unserialize($models);
			foreach($models as $model) {
				list($object_model, $m) = $model;
				require_once $m;
				self::$models[$object_model] = new $object_model();
			}
    	    
        } else {
			$models = array();
            
			foreach(new DirectoryIterator(APP_PATH . 'models') as $model) {
                if($model->isDot() || $model->isDir())
                    continue;

                require_once $model->getPathname();
                $object_model = str_replace('.php', '', $model->getFilename());
                $object_model = Util::camelcase($object_model);
				$models[] = array($object_model,$model->getPathname());
		    
				if (!class_exists($object_model)) {
					throw new KumbiaException("No se encontr&oacute; la Clase \"$object_model\"", "Es necesario definir una clase en el modelo
							'$model' llamado '$object_model' para que esto funcione correctamente.");
				} else {
					self::$models[$object_model] = new $object_model();
					if (!is_subclass_of(self::$models[$object_model], 'ActiveRecord')) {
						throw new KumbiaException("Error inicializando modelo \"$object_model\"", "El modelo '$model' debe ser una clase o sub-clase de ActiveRecord");
					}
					if (!self::$models[$object_model]->get_source()) {
						self::$models[$object_model]->set_source($model);
					}
				}
            }
			
			Cache::save(serialize($models), Config::get('config.application.metadata_lifetime'), 'models', 'kumbia');
        }

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
     * Metodo para incluir las librerias de Framework
     *
     */
    private static function _load_library_kumbia(){
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
	/**
	 * Obtiene un modelo
	 *
	 * @param string $model
	 * @return ActiveRecord
	 **/
	public static function model($model)
	{
		if(isset(self::$models[$model])) {
			return self::$models[$model];
		}
		
		if(!class_exists($model)) {
			$model_file = APP_PATH . 'models/' . Util::smallcase($model). '.php';
			include $model_file;
		}
		
		self::$models[$model] = new $model();
		return self::$models[$model];
	}
}