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
 * @copyright  Copyright (c) 2005 - 2026 KumbiaPHP Team (http://www.kumbiaphp.com)
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
     * Normaliza un nombre relativo usado por las consolas generadoras.
     *
     * @param string $path Ruta relativa recibida por consola
     *
     * @throws KumbiaException
     * @return string Ruta relativa segura con separador /
     */
    public static function normalizeRelativePath(string $path): string
    {
        if (
            $path === ''
            || strpos($path, "\0") !== false
            || strpos($path, '\\') !== false
            || $path[0] === '/'
            || preg_match('/^[a-zA-Z]:/', $path)
        ) {
            throw new KumbiaException('La ruta indicada no es segura');
        }

        $path = trim($path, '/');
        $segments = explode('/', $path);
        foreach ($segments as $segment) {
            if ($segment === '' || $segment === '.' || $segment === '..') {
                throw new KumbiaException('La ruta indicada no es segura');
            }
        }

        return implode('/', $segments);
    }

    /**
     * Resuelve una ruta relativa segura dentro de un directorio base.
     *
     * @param string $basePath Directorio autorizado
     * @param string $path Ruta relativa recibida por consola
     * @param string $suffix Sufijo opcional para el archivo final
     *
     * @throws KumbiaException
     * @return string Ruta segura dentro del directorio base
     */
    public static function resolveRelativePath(string $basePath, string $path, string $suffix = ''): string
    {
        $relativePath = str_replace('/', DIRECTORY_SEPARATOR, self::normalizeRelativePath($path));
        $basePath = rtrim($basePath, '/\\');
        $targetPath = $basePath . DIRECTORY_SEPARATOR . $relativePath . $suffix;
        $baseRealPath = realpath($basePath);

        if ($baseRealPath !== false) {
            $checkPath = $targetPath;
            while (($checkRealPath = realpath($checkPath)) === false) {
                $parentPath = dirname($checkPath);
                if ($parentPath === $checkPath) {
                    break;
                }
                $checkPath = $parentPath;
            }

            if ($checkRealPath !== false) {
                $baseRealPath = rtrim($baseRealPath, '/\\') . DIRECTORY_SEPARATOR;
                $checkRealPath = rtrim($checkRealPath, '/\\') . DIRECTORY_SEPARATOR;
                if (strpos($checkRealPath, $baseRealPath) !== 0) {
                    throw new KumbiaException('La ruta indicada no es segura');
                }
            }
        }

return $targetPath;
    }

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
