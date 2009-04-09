<?php
/**
 * Kumbia PHP Framework
 *
 * LICENSE
 *
 * This source file is subject to the GNU/GPL that is bundled
 * with this package in the file docs/LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.kumbia.org/license.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kumbia@kumbia.org so we can send you a copy immediately.
 *
 * @category  Kumbia
 * @package   Cache
 * @copyright Copyright (c) 2005-2007 Andres Felipe Gutierrez (andresfelipe at vagoogle.net)
 * @copyright Copyright (C) 2007-2007 Roger Jose Padilla Camacho(rogerjose81 at gmail.com)
 * @copyright Copyright (C) 2007-2009 Deivinson Jose Tejeda Brito(deivinsontejeda at gmail.com)
 * @license   http://www.kumbia.org/license.txt GNU/GPL
 */

/**
 * Clase que implementa un componente de cacheo
 * 
 * @category  Kumbia
 * @package   Cache
 * @copyright Copyright (c) 2005-2007 Andres Felipe Gutierrez (andresfelipe at vagoogle.net)
 * @copyright Copyright (C) 2007-2007 Roger Jose Padilla Camacho(rogerjose81 at gmail.com)
 * @copyright Copyright (C) 2007-2009 Deivinson Jose Tejeda Brito(deivinsontejeda at gmail.com)
 * @license   http://www.kumbia.org/license.txt GNU/GPL
 */
class FileCache implements CacheInterface
{
    /**
     * Obtiene el nombre de archivo a partir de un id y grupo
     *
     * @param string $id
     * @param string $group
     * @return string
     **/
    protected static function _get_filename($id, $group)
    {
        return 'cache_'.md5($id).'.'.md5($group);
    }
	/**
	 * Carga un elemento cacheado
	 *
	 * @param string $id
	 * @param string $group
	 * @return string
	 */
	public static function get($id, $group) 
    {
        $filename = APP_PATH . 'temp/cache/'.self::_get_filename($id, $group);
		if(file_exists($filename)){
            $fh = fopen($filename, 'r');
            
            $lifetime = trim(fgets($fh));
			if($lifetime == 'undefined' || $lifetime >= time())
                $data = stream_get_contents($fh);
            else
                $data = null;
            
            fclose($fh);
			return $data;
		}
        return null;
    }
	/**
	 * Guarda un elemento en la cache con nombre $id y valor $value
	 *
	 * @param string $id
	 * @param string $group
	 * @param string $value
	 * @param int $lifetime tiempo de vida en forma timestamp de unix
	 * @return boolean
	 */
	public static function save($id, $group, $value, $lifetime)
    {
        if($lifetime == null)
            $lifetime = 'undefined';
        return file_put_contents(APP_PATH . 'temp/cache/'.self::_get_filename($id, $group), "$lifetime\n$value");
    }
	/**
	 * Limpia la cache
	 *
	 * @param string $group
	 * @return boolean
	 */
	public static function clean($group=false)
    {
        $pattern = $group ? APP_PATH . 'temp/cache/'.'*.'.md5($group) : APP_PATH . 'temp/cache/'.'*';
        foreach (glob($pattern) as $filename) {
            if(!unlink($filename)) {
                return false;
            }
        }
    }
	/**
	 * Elimina un elemento de la cache
	 *
	 * @param string $id
	 * @param string $group
	 * @return string
	 */
	public static function remove($id, $group)
    {
        return unlink(APP_PATH . 'temp/cache/'.self::_get_filename($id, $group));
    }
}