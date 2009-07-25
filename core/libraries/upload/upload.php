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
 * Sube archivos al servidor
 * 
 * @category   Kumbia
 * @package    Upload 
 * @copyright  Copyright (c) 2005-2009 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */
class Upload
{
	/**
	 * Sube un archivo a la ruta indicada si esta en $_FILES
	 *
	 * @param string $name nombre del archivo en el formulario
     * @param string $path ruta donde se subira. Ejemplo: /var/www/public/app/temp/files/
     * @param string $new_name indica el nuevo nombre para el archivo 
	 * @return string
	 */
	public static function file_in_path($name, $path, $new_name=null)
    {
		if(isset($_FILES[$name])){
			if(!$new_name) {
				$new_name = $_FILES[$name]['name'];
			}
			return move_uploaded_file($_FILES[$name]['tmp_name'], $path . $new_name);
		} else {
			return false;
		}
	}
    
	/**
	 * Sube un archivo al directorio app/public/files/upload si esta en $_FILES
	 *
	 * @param string $name nombre del archivo en el formulario
     * @param string $new_name indica el nuevo nombre para el archivo 
	 * @return string
	 */
	public static function file($name, $new_name=null)
    {
		return self::file_in_path($name, APP_PATH . 'public/files/upload/', $new_name);
	}
    
	/**
	 * Sube un archivo al directorio app/public/img/upload si esta en $_FILES
	 *
	 * @param string $name nombre del archivo en el formulario
     * @param string $new_name indica el nuevo nombre para el archivo 
	 * @return boolean
	 */
	public static function image($name, $new_name=null)
    {
		return self::file_in_path($name, APP_PATH . 'public/img/upload/', $new_name);
	}
}