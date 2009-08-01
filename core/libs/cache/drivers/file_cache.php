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
 * Cacheo de Archivos
 * 
 * @category   Kumbia
 * @package    Cache
 * @subpackage Drivers 
 * @copyright  Copyright (c) 2005-2009 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
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
	public function get($id, $group) 
    {
        $filename = APP_PATH . 'temp/cache/'.self::_get_filename($id, $group);
		if(file_exists($filename)){
            $fh = fopen($filename, 'r');
            
            $lifetime = trim(fgets($fh));
			if($lifetime == 'undefined' || $lifetime >= time()) {
                $data = stream_get_contents($fh);
            } else{
                $data = null;
            }
            
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
	public function save($id, $group, $value, $lifetime)
    {
        if($lifetime == null) {
            $lifetime = 'undefined';
        }
        return file_put_contents(APP_PATH . 'temp/cache/'.self::_get_filename($id, $group), "$lifetime\n$value");
    }
	/**
	 * Limpia la cache
	 *
	 * @param string $group
	 * @return boolean
	 */
	public function clean($group=false)
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
	public function remove($id, $group)
    {
        return unlink(APP_PATH . 'temp/cache/'.self::_get_filename($id, $group));
    }
}