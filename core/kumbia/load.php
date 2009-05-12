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
 * Cargador
 * 
 * @category   Kumbia
 * @package    Kumbia
 * @copyright  Copyright (c) 2005-2009 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

/**
 * Cargador perezoso
 *
 * @category  Kumbia
 * @package   Kumbia
 * @author    Kumbia Team (http://www.kumbiaphp.com)
 * @license   http://wiki.kumbiaphp.com/Licencia     New BSD License
 */
class Load 
{
	/**
	 * Modelos inyectados en los controladores
	 *
	 * @var array
	 **/
	protected static $_injected_models = array();
	/**
	 * Carga las extensions
	 *
	 * @param string $extension
	 * @throw KumbiaException
	 **/
	public static function extensions($extension) 
	{
		$args = func_get_args();
		foreach($args as $extension) {
			$file = CORE_PATH . "extensions/$extension/$extension.php";
			if(is_file($file)) {
				include_once $file;
			} else {
				throw new KumbiaException("Extension $extension no encontrada");
			}
		}
	}
	/**
	 * Carga librerias de terceros
	 *
	 * @param string $vendor
	 * @throw KumbiaException
	 **/
	public static function vendors($vendor) 
	{
		$args = func_get_args();
		foreach($args as $vendor) {
			$file = CORE_PATH . "vendors/$vendor.php";
			if(is_file($file)) {
				include_once $file;
			} else {
				throw new KumbiaException("Libreria $vendor no encontrada");
			}
		}
	}
	/**
	 * Carga los helpers
	 *
	 * @param string $helper
	 * @throw KumbiaException
	 **/
	public static function helpers($helper) 
	{
		$args = func_get_args();
		foreach($args as $helper) {
			/**
			 * Carga helper de kumbia
			 **/
			$file = CORE_PATH . "helpers/$helper.php";
			if(is_file($file)) {
				include_once $file;
			} else {
				/**
				 * Carga helper de usuario
				 **/
				$file = APP_PATH . "helpers/$helper.php";
				if(is_file($file)) {
					include_once $file;
				} else {
					throw new KumbiaException("Helper $helper no encontrado");
				}
			}
		}
	}
	/**
	 * Carga modelos
	 *
	 * @param string $model
	 * @throw KumbiaException
	 **/
	public static function models($model) 
	{
		$args = func_get_args();
		$controller = Dispatcher::get_controller();
		
		foreach($args as $model) {
			/**
			 * Nombre de la clase
			 **/
			$Model = Util::camelcase(basename($model));
		
			/**
			 * Verifico si esta cargada la clase
			 **/
			if(!class_exists($Model, false)) {
				$file = APP_PATH . "models/$model.php";
				if(is_file($file)) {
					include $file;
				} else {
					throw new KumbiaException("Modelo $model no encontrado");
				}
			}
			
			if($controller) {
				$controller->$Model = new $Model();
				self::$_injected_models[] = $Model;
			}
		}
	}
	/**
	 * Carga modelos desde un directorio
	 *
	 * @param string $dir directorio de modelos
	 * @param string $model modelo a cargar
	 * @throw KumbiaException
	 **/
	public static function models_dir($dir, $model=null) 
	{
		$controller = Dispatcher::get_controller();
		
		/**
		 * Verifica si se indicaron modelos especificos
		 **/
		if($model) {
			$args = func_get_args();
			
			while($model = next($args)) {
				/**
				 * Nombre de la clase
				 **/
				$Model = Util::camelcase(basename($model));
				
				/**
				 * Verifica si esta cargada la clase
				 **/
				if(!class_exists($Model, false)) {
					$file = APP_PATH . "models/$dir/$model.php";
					if(is_file($file)) {
						include $file;
					} else {
						throw new KumbiaException("Modelo $dir/$model no encontrado");
					}
				}
				
				if($controller) {
					$controller->$Model = new $Model();
					self::$_injected_models[] = $Model;
				}
			}
		} else{
			foreach(new DirectoryIterator(APP_PATH . "models/$dir") as $file) {
				if($file->isFile()) {
					include_once $file->getPathname();

					if($controller) {
						$Model = Util::camelcase(basename($file->getFilename(), '.php'));
						$controller->$Model = new $Model();
						self::$_injected_models[] = $Model;
					}
				}
			}
		}
	}
	/**
	 * Carga todos los modelos
	 *
	 **/
	public static function all_models()
	{
		$controller = Dispatcher::get_controller();
		
		foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator(APP_PATH . 'models')) as $file) {
			if($file->isFile()) {
				include_once $file->getPathname();
				
				if($controller) {
					$Model = Util::camelcase(basename($file->getFilename(), '.php'));
					$controller->$Model = new $Model();
					self::$_injected_models[] = $Model;
				}
			}
		}
	}
	/**
	 * Obtiene los nombres de modelos inyectados en los controladores
	 * en notacion CamelCase (es decir el nombre de clase)
	 *
	 * @return array
	 **/
	public static function get_injected_models()
	{
		return self::$_injected_models;
	}
	/**
	 * Limpia el buffer de modelos inyectados
	 *
	 **/
	public static function reset_injected_models()
	{
		self::$_injected_models = array();
	}
	/**
	 * Inicia el boot
	 *
	 **/
	public static function boot()
	{
		$boot = Config::read('boot.ini');
		if($boot['modules']['vendors']){
			$vendors = explode(',', str_replace(' ', '', $boot['modules']['vendors']));
			foreach ($vendors as $vendor){
				require_once VENDORS_PATH . "$vendor.php";
			}
			unset($vendors);
		}
		if($boot['modules']['extensions']){
			$extensions = explode(',', str_replace(' ', '', $boot['modules']['extensions']));
			foreach ($extensions as $extension){
				require_once CORE_PATH . "extensions/$extension" .'/'.$extension.'.php';
			}
			unset($extensions);
		}
	}
}
