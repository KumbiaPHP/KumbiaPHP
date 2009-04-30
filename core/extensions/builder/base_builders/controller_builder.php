<?php
/**
 * Kumbia PHP Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://XXXXXXXX
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Kumbia
 * @package    Builder
 * @copyright  Copyright (c) 2005-2009 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * Builder para controllers
 *
 * @category  Kumbia
 * @package   Builder
 * @copyright  Copyright (c) 2005-2009 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version   SVN:$id
 */
class ControllerBuilder implements BuilderInterface
{
	/**
 	 * Ejecuta el builder
     *
	 * @param string $name elemento a construir
 	 * @param array $params
 	 * @return boolean
     * @throw BuilderException
 	 */
	public static function execute($name, $params)
    {
	
		$path = APP_PATH . 'controllers/';
		if(isset($params['module']) && $params['module']) {
			$path .= "{$params['module']}/";
		}
		if(!is_dir($path)) {
			if(!Util::mkpath($path)) {
				throw new KumbiaException("No se ha logrado generar la ruta para controllers $path");
			}
		}
		
		$controller = Util::camelcase($name);
		$scontroller = Util::smallcase($name);
		/**
		 * Nombre de archivo
		 **/
		$__file__ = $path . "{$scontroller}_controller.php";
			
		/**
		 * Generando archivo
		 **/
		if(!file_exists($__file__)) {
			extract($params);
			
			echo "\r\n-- Generando controller: $controller\r\n$__file__\r\n";
			
			ob_start();
			echo "<?php\n";
			include CORE_PATH . 'extensions/builder/base_builders/templates/controller.php';
			$code = ob_get_contents();
			ob_end_clean();

			if(!file_put_contents($__file__, $code)) {
				throw new KumbiaException("No se ha logrado generar el archivo de controller $__file__");
			}
			
			$path = APP_PATH . 'views/';
			if(isset($params['module']) && $params['module']) {
				$path .= "{$params['module']}/";
			}
			$path .= $scontroller;
			
			if(!is_dir($path)) {
				echo "\r\n-- Generando directorio de vistas: \r\n$path\r\n";
				
				if(!is_dir($path)) {
					if(!Util::mkpath($path)) {
						throw new KumbiaException("No se ha logrado generar la ruta para controllers $path");
					}
				}
			}
		} else {
			echo "\r\n-- El controller ya existe en $__file__\r\n";
		}
		return true;
	}
}