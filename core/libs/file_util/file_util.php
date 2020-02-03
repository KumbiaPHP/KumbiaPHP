<?php
/**
 * KumbiaPHP web & app Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @category   Kumbia
 * @package    Core
 *
 * @copyright  Copyright (c) 2005 - 2020 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Utilidades para el manejo de ficheros y directorios
 * @category   Kumbia
 * @package    Core
 */
class FileUtil
{
    /**
     * Crea un path en caso de que no exista
     *
     * @param string $path ruta a crear
     * @todo Se debe optimizar
     * @return boolean
     */
    public static function mkdir($path)
    {
        if (file_exists($path) || @mkdir($path))
            return TRUE;
        return (self::mkdir(dirname($path)) && mkdir($path));
    }

    /**
     * Elimina un directorio.
     *
     * @param string $dir ruta de directorio a eliminar
     * @todo Se debe optimizar
     * @return boolean
     */
    public static function rmdir($dir)
    {
        // Obtengo los archivos en el directorio a eliminar
        if ($files = array_merge(glob("$dir/*"), glob("$dir/.*"))) {
            // Elimino cada subdirectorio o archivo
            foreach ($files as $file) {
                // Si no son los directorios "." o ".."
                if (!preg_match("/^.*\/?[\.]{1,2}$/", $file)) {
                    if (is_dir($file)) {
                        return self::rmdir($file);
                    } elseif (!@unlink($file)) {
                        return FALSE;
                    }
                }
            }
        }
        return @rmdir($dir);
    }
}
