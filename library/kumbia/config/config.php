<?php
/**
 * Kumbia PHP Framework
 * PHP version 5
 * 
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
 * @package   Config
 * @author    Andres Felipe Gutierrez <andresfelipe@vagoogle.net>
 * @copyright 2008-2008 Emilio Rafael Silveira Tovar <emilio.rst at gmail.com>
 * @copyright 2007-2008 Deivinson Jose Tejeda Brito <deivinsontejeda at gmail.com>
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt GNU/GPL
 * @version   SVN:$id
 */

/**
 * @see ConfigException
 */
require_once CORE_PATH . 'config/config_exception.php';

/**
 * Clase para la carga de Archivos .INI y de configuración
 *
 * Aplica el patrón Singleton que utiliza un array
 * indexado por el nombre del archivo para evitar que
 * un .ini de configuración sea leido mas de una
 * vez en runtime con lo que aumentamos la velocidad.
 *
 * @category  Kumbia
 * @package   Config
 * @author    Andres Felipe Gutierrez <andresfelipe@vagoogle.net>
 * @copyright 2008-2008 Emilio Rafael Silveira Tovar <emilio.rst at gmail.com>
 * @copyright 2007-2008 Deivinson Jose Tejeda Brito <deivinsontejeda at gmail.com>
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt GNU/GPL
 * @access    public
 */
class Config
{
	/**
	 * Contenido de variables de configuracion
	 *
	 * @var array
	 */
	protected static $_vars = array();
    /**
     * Obtiene un atributo de configuracion
     *
     * @param string $var nombre de variable de configuracion
     * @return mixed
     **/
    public static function & get($var) 
    {
        $namespaces = explode('.', $var);
        switch(count($namespaces)) {
            case 3:
                if(isset(self::$_vars[$namespaces[0]][$namespaces[1]][$namespaces[2]]))
                    $value = self::$_vars[$namespaces[0]][$namespaces[1]][$namespaces[2]];
                break;
            case 2:
                if(isset(self::$_vars[$namespaces[0]][$namespaces[1]]))
                    $value = self::$_vars[$namespaces[0]][$namespaces[1]];
                break;
            case 1:
                if(isset(self::$_vars[$namespaces[0]]))
                    $value = self::$_vars[$namespaces[0]];
                break;
            default:
                $value = false;
        }
        return $value;
    }
    /**
     * Asigna un atributo de configuracion
     *
     * @param string $var variable de configuracion
     * @param mixed $value valor para atributo
     **/
    public static function set($var, $value)
    {
        $namespaces = explode('.', $var);
        switch(count($namespaces)) {
            case 3:
                self::$_vars[$namespaces[0]][$namespaces[1]][$namespaces[2]] = $value;
                break;
            case 2:
                self::$_vars[$namespaces[0]][$namespaces[1]] = $value;
                break;
            case 1:
                self::$_vars[$namespaces[0]] = $value;
                break;
        }
    }
	/**
	 * Lee un archivo de configuracion
	 *
     * @param $file archivo .ini
     * @param boolean $force forzar lectura de .ini
	 * @return array
	 */
	public static function & read($file, $force=false)
    {
        $namespace = basename($file, '.ini');
        
		if(isset(self::$_vars[$namespace]) && !$force)
			return self::$_vars[$namespace];
		
		if(!file_exists(APP_PATH."config/$file")){
			throw new ConfigException("No existe el archivo de configuraci&oacute;n $file");
		}

        self::$_vars[$namespace] = parse_ini_file(APP_PATH."config/$file", true);
		return self::$_vars[$namespace];
	}
}