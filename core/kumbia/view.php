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
	protected static $_content = '';
	/**
	 * Renderiza la vista
	 *
	 * @param Controller $controller
	 * @param string $url url a renderizar
	 */
	public static function render($controller, $_url)
	{
        // Carga los helpers desde el boot.ini
        if(Config::get('boot.modules.helpers')) {
			$helpers = explode(',', Config::get('boot.modules.helpers'));
			foreach($helpers as $helper){
				self::helpers(trim($helper));
			}
        }
        
        // Mapea los atributos del controller en el scope
        extract(get_object_vars($controller), EXTR_OVERWRITE);

        // Intenta cargar la vista desde la cache si esta en producion, si no renderiza
        if(!PRODUCTION || ($scaffold) || $cache['type']!='view' || !(self::$_content = Cache::get($_url, "$controller_name.$action_name.$id"))) {
            // Carga el el contenido del buffer de salida
			self::$_content = ob_get_clean();
				
			if ($module_name){
				$controller_views_dir =  APP_PATH . "views/$module_name/$controller_name";
			} else {
				$controller_views_dir =  APP_PATH . "views/$controller_name";
			}
            if($response && $response != 'view'){
                 $controller_views_dir = "$controller_views_dir/_$response";
            }
            
            // Renderizar vista
			if($view) {
				ob_start();
                
                $file = "$controller_views_dir/$view.phtml";
                if(!is_file($file)) {
					if($scaffold) {
						$file = APP_PATH . "views/_shared/scaffolds/$scaffold/$view.phtml";
							if(!is_file($file)) throw new KumbiaException("Vista $view.phtml no encontrada");
					} else {
						throw new KumbiaException("Vista $view.phtml no encontrada");
						}
                }
                include $file;
                //verifica si no se ha especificado un grupo para la cache
				if(!$cache['group']){
				    $cache['group'] = "$controller_name.$action_name";//.$id";
                }
				if(PRODUCTION && $cache['type'] == 'view') {
				    Cache::save(ob_get_contents(), $cache['time'], $_url, $cache['group']);
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
			$template = APP_PATH . "views/_shared/templates/$template.phtml";
		} else {
			$template = APP_PATH . "views/_shared/templates/$controller_name.phtml";
		}
		
		if(is_file($template)) {
			ob_start();
			include $template;
				
			if(PRODUCTION && $cache['type'] == 'template') {
				Cache::save(ob_get_contents(), $cache['time'], $_url, "kumbia.templates");
			}
			ob_end_flush();
			return;
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
	public static function partial($partial, $time=false, $params=array())
	{
		if(PRODUCTION && $time) {
		    $data = Cache::start($time, $partial, 'kumbia.partials');
			if($data) {
				echo $data;
				return;
			}
		}
		
		if(is_string($params)) {
			$params = Util::get_params($params);
		}
		extract ($params);
		
		$path = "views/_shared/partials/$partial.phtml";
		
		//Verificando el partials en el dir app 
		$file = APP_PATH . $path;
		
		if(!is_file($file)){
		    //Verificando el partials en el dir core
			$file = CORE_PATH . "views/partials/$partial.phtml";
			if(!is_file($file)){
                if(PRODUCTION && $time!==false) {
                    Cache::end(false);
                }
                throw new KumbiaException('Vista Parcial "'.$file.'" no se encontro');
			}
		}
		include $file;
        if(PRODUCTION && $time) {
            Cache::end();
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
                $file = CORE_PATH . $path;
                if (! is_file($file)) {
                    throw new KumbiaException("Helpers $helper no encontrado");
                }
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

spl_autoload_register('View::helpers');