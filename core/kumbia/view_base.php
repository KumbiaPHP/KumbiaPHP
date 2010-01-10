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
 * Renderer de vistas
 * 
 * @category   Kumbia
 * @package    Core 
 * @copyright  Copyright (c) 2005-2009 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */
//Carga la clase view que la extiende
// @see View
require APP_PATH . 'view.php';

class ViewBase {
	/**
	 * Contenido
	 *
	 * @var string
	 **/
	protected static $_content;
	/**
	 * Vista a renderizar
	 *
	 * @var string
	 **/
	protected static $view;
	/**
	* Template
	*
	* @var string
	*/
	protected static $template = 'default';
	/**
	 * Indica el tipo de salida generada por el controlador
	 *
	 * @var string
	 */
	protected static $response;
	/**
	 * Indica el path al que se le añadira la constante correspondiente
	 *
	 * @var string
	 */
	protected static $path;
	/**
	 * Número de minutos que será cacheada la vista actual
	 *
	 * type: tipo de cache (view, template)
	 * time: tiempo de vida de cache
	 *
	 * @var array
	 */
	
	protected static $cache = array('type' => FALSE, 'time' => FALSE, 'group'=>FALSE);
	/**
	 * Cambia el view y opcionalmente el template
	 *
	 * @param string $view nombre del view a utilizar sin .phtml
	 * @param string $template	opcional nombre del template a utilizar sin .phtml
	 *
	 * @deprecated Ahora View::render
	 */
	public static function render($view, $template = FALSE){
		self::$view = $view;
		if($template === FALSE) return;
		self::$template = $template;
	}
	public static function template($template){
		self::$template = $template;
	}
	/**
	 * Indica el tipo de Respuesta dada por el controlador
	 * buscando el view con esa extension.
	 * ej. View::response('xml');
	 * buscara: views/controller/action.xml.phtml
	 *
	 * @param string $type
	 * @param string $template Opcional nombre del template sin .phtml
	 *
	 * @deprecated Ahora View::response
	 */
	public static function response($response, $template = FALSE){
		self::$response = $response;
		if($template === FALSE) return;
		self::$template = $template;
	}
	
	public static function setPath($path) {
		self::$path = $path;
	}
	
	public static function get($atribute) {
		return self::${$atribute};
	}
	/**
	 * Asigna cacheo de vistas o template
	 *
	 * @param $time tiempo de vida de cache
	 * @param $type tipo de cache (view, template)
	 */
	public static function cache($time, $type='view', $group=FALSE) {
		if($time !== FALSE) {
			self::$cache['type'] = $type;
			self::$cache['time'] = $time;
			self::$cache['group'] = $group;
		} else {
			self::$cache['type'] = FALSE;
		}
	}
	/**
	 * Renderiza la vista
	 *
	 * @param Controller $controller
	 * @param string $url url a renderizar
	 */
	public static function output(Controller $controller, /*Router*/ $_url)
	{
	if(!self::$view && !self::$template){
		return ob_end_flush(); 
	}
        // Mapea los atributos del controller en el scope
        extract(get_object_vars($controller), EXTR_OVERWRITE);

		// inicia contenido con valor nulo
		self::$_content = null;

		// si se encuentra en produccion
		if(PRODUCTION) {
			// obtiene el driver de cache
			$cache_driver = Cache::factory();
			
			// si se cachea vista
			if($cache['type'] == 'view') {
				// el contenido permanece nulo si no hay nada cacheado o la cache expiro
				self::$_content = $cache_driver->get($_url, $cache['group']);
			}
		}

        // carga la vista si no esta en produccion o se usa scaffold o no hay contenido cargado
        if(!PRODUCTION || $scaffold || !self::$_content) {
            // Carga el contenido del buffer de salida
			self::$_content = ob_get_clean();

            // Renderizar vista
		if($view = self::$view) {
			ob_start();
		if(self::$response && !self::$response == 'view') {
			$file = APP_PATH.'views/'.self::$path."/$view.".self::$response.'.phtml';
		} else  $file = APP_PATH.'views/'.self::$path."/$view.phtml";
                if(!is_file($file) && $scaffold) {
					$file =APP_PATH ."views/_shared/scaffolds/$scaffold/$view.phtml";
                }
				
				// carga la vista
                if (!include $file) throw new KumbiaException("Vista $view.phtml no encontrada");
                
				// si esta en produccion y se cachea la vista
				if(PRODUCTION && $cache['type'] == 'view') {
				    $cache_driver->save(ob_get_contents(), $cache['time'], $_url, $cache['group']);
			    }
			    
                // Verifica si se debe renderizar solo la vista
		        if(self::$response == 'view' || self::$response == 'xml') {
			        ob_end_flush();
			        return;
		        }
		        
		        self::$_content = ob_get_clean();
			}
		} else {
            ob_clean();
        }
	
        // Renderizar template
		if($template = self::$template) {
			ob_start();
				
			// carga el template
			if (!include APP_PATH . "views/_shared/templates/$template.phtml") throw new KumbiaException("Template $template no encontrado");
			
			// si esta en produccion y se cachea template
			if(PRODUCTION && $cache['type'] == 'template') {
				$cache_driver->save(ob_get_contents(), $cache['time'], $_url, "kumbia.templates");
			}
			
			return ob_end_flush();
		}
		
		echo self::$_content;
	}
	
	/**
	 * Imprime el contenido del buffer
	 *
	 */
	public static function content()
	{
		echo self::$_content;
	}
	
	/**
	 * Renderiza una vista parcial
	 *
	 * @param string $partial vista a renderizar
	 * @param string $time tiempo de cache
	 * @param array $params
	 * @return string
	 * @throw KumbiaException
	 */
	public static function partial($partial, $__time=FALSE, $params=array())
	{
		if(PRODUCTION && $__time) {
			// obtiene el driver de cache
			$cache = Cache::factory();
		
		    $data = $cache->start($__time, $partial, 'kumbia.partials');
			if($data) {
				echo $data;
				return;
			}
		}
		
		//Verificando el partials en el dir app 
		$__file = APP_PATH . "views/_shared/partials/$partial.phtml";
		
		if(!is_file($__file)){
		    //Verificando el partials en el dir core
			$__file = CORE_PATH . "views/partials/$partial.phtml";
		}
		
		if(is_string($params)) {
			$params = Util::get_params($params);
		}
		
		// carga los parametros en el scope
		extract ($params, EXTR_OVERWRITE);
		
		// carga la vista parcial
		if (!include $__file) throw new KumbiaException('Vista Parcial "'.$__file.'" no se encontro');
		
		// se guarda en la cache de ser requerido
        if(PRODUCTION && $__time) {
            $cache->end();
        }
		
	}
	
	/**
     * Carga los helpers
     *
     * @param string $helper
     * @throw KumbiaException
     **/
    public static function helpers ($helper)
    {
            $helper = Util::smallcase($helper);
			$path = "extensions/helpers/$helper.php";
            $file = APP_PATH . $path;
			if (! is_file($file)) {
				if (!include CORE_PATH . $path) throw new KumbiaException("Helpers $helper no encontrado");
			return;
            }
            require $file;
    }
}

/**
 * Atajo para htmlspecialchars, por defecto toma el charset de la
 * aplicacion
 *
 * @param string $s
 * @param string $charset
 * @return string
 **/
function h($s, $charset = APP_CHARSET) {
    
    return htmlspecialchars($s, ENT_QUOTES, $charset);
}

/**
 * Atajo para echo + htmlspecialchars, por defecto toma el charset de la
 * aplicacion
 *
 * @param string $s
 * @param string $charset
 * @return string
 **/
function eh($s, $charset = APP_CHARSET) {
    
    echo htmlspecialchars($s, ENT_QUOTES, $charset);
}

// registra la autocarga de helpers
spl_autoload_register('View::helpers');