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
 * destroyer para los files
 *
 * @category   Kumbia
 * @package    modules
 * @copyright  Copyright (c) 2005-2009 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */
class FilterDestroyer implements DestroyerInterface
{
	/**
 	 * Ejecuta el destroyer
     *
	 * @param string $name elemento a destruir
 	 * @param array $params
 	 * @return boolean
     * @throw KumbiaException
 	 */
	public static function execute($name, $params)
    {
		$filter = Util::camelcase($name);
		$sfilter = Util::smallcase($name);
		/**
		 * Nombre de archivo
		 **/
		$file = APP_PATH . "filters/{$sfilter}_filter.php";
		
		echo "\r\n-- Eliminando filter: $filter\r\n$file\r\n";
		
		if(!unlink($file)) {
			throw new KumbiaException("No se ha logrado eliminar el archivo $file");
		}

		return true;
	}
}