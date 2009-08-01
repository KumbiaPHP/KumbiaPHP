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
 * Builder para filter
 * 
 * @category   Kumbia
 * @package    Builder
 * @subpackage BaseBuilder
 * @copyright  Copyright (c) 2005-2009 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */
class FilterBuilder implements BuilderInterface
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
		$filter = Util::camelcase($name);
		$sfilter = Util::smallcase($name);
		/**
		 * Nombre de archivo
		 **/
		$__file__ = APP_PATH . 'filters/' . "{$sfilter}_filter.php";
			
		/**
		 * Generando archivo
		 **/
		if(!file_exists($__file__)) {
			extract($params);
			
			echo "\r\n-- Generando filter: $filter\r\n$__file__\r\n";
			
			ob_start();
			echo "<?php\n";
			include CORE_PATH . 'libs/builder/base_builders/templates/filter.php';
			$code = ob_get_contents();
			ob_end_clean();

			if(!file_put_contents($__file__, $code)) {
				throw new KumbiaException("No se ha logrado generar el archivo de filter $__file__");
			}
		} else {
			echo "\r\n-- El filter ya existe en $__file__\r\n";
		}
		return true;
	}
}