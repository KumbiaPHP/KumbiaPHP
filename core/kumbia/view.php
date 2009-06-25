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
        /**
         * @see Tags
         */
        require CORE_PATH . 'components/helpers/tags.php';
        
        /**
         * Mapea los atributos del controller en el scope
         *
         **/
        extract(get_object_vars($controller), EXTR_OVERWRITE);
		
		/**
		 * Intenta cargar la vista desde la cache
		 **/
		self::$_content = $cache['type']=='view' ? Cache::get($_url, 'kumbia.views') : '';
		if(!self::$_content) {
			/**
			 * Carga el el contenido del buffer de salida
			 *
			 **/
			self::$_content = ob_get_clean();
				
			if ($module_name){
				$controller_views_dir =  APP_PATH . "views/$module_name/$controller_name";
			} else {
				$controller_views_dir =  APP_PATH . "views/$controller_name";
			}
            if($response != 'view'){
                 $controller_views_dir =  APP_PATH . "views/$module_name/$controller_name/$response/";
            }
			/**
			 * Renderizar vista
			 *
			 **/
			if($view) {
				ob_start();
				include "$controller_views_dir/$view.phtml";
				
				if($cache['type'] == 'view') {
				    Cache::save(ob_get_contents(), $cache['time'], $_url, 'kumbia.views');
			    }
			    
			    /**
		         * Verifica si se debe renderizar solo la vista
		         *
		         **/
		        if($response == 'view' || $response == 'xml') {
			        ob_end_flush();
			        return;
		        }
		        
		        self::$_content = ob_get_clean();
			}
		}
	
		/**
		 * Renderizar template
		 *
		 **/
		if($template) {
			$template = APP_PATH . "views/templates/$controller->template.phtml";
		} else {
			$template = APP_PATH . "views/templates/$controller_name.phtml";
		}
		
		if(is_file($template)) {
			ob_start();
			include $template;
				
			if($cache['type'] == 'template') {
				Cache::save(ob_get_contents(), $cache['time'], $_url, 'kumbia.templates');
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
		if($time!==false) {
			if($data = Cache::start($time, $partial, 'kumbia.partials')) {
				echo $data;
				return;
			}
		}
		
		if(is_string($params)) {
			$params = Util::get_params($params);
		}
		extract ($params);
		
		$partial_file = APP_PATH . "views/partials/$partial.phtml";
		
		if(is_file($partial_file)){
			include $partial_file;
			if($time!==false) {
				Cache::end();
			}
		} else {
			if($time!==false) {
				Cache::end(false);
			}
			throw new KumbiaException('Kumbia no puede encontrar la vista parcial: "'.$partial_file.'"', 0);
		}
	}
}
