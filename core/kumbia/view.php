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
class View
{
	/**
	 * Contenido
	 *
	 * @var string
	 **/
	protected static $_content;
	
	/**
	 * Renderiza la vista
	 *
	 * @param Controller $controller
	 * @param string $url url a renderizar
	 */
	public static function render($controller, $_url)
	{
        // Carga los helpers desde el boot.ini
        if($config = Config::get('boot.modules.helpers')) {
			foreach(explode(',', $config) as $helper){
				self::helpers(trim($helper));
			}
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
			if($view) {
				ob_start();
				
                $file =APP_PATH ."views/$controller_path/$view.phtml";
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
		        if($response == 'view' || $response == 'xml') {
			        ob_end_flush();
			        return;
		        }
		        
		        self::$_content = ob_get_clean();
			}
		} else {
            ob_clean();
        }
	
        // Renderizar template
		if($template) {
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