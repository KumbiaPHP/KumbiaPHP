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
 * @category   KumbiaPHP
 * @package    Helpers
 * @copyright  Copyright (c) 2005 - 2017 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

/**
 * Helper que utiliza Css
 *
 * @category   KumbiaPHP
 * @package    Helpers
 */
class Css
{
    /**
     * Css que son requisito de otros
     *
     * @var array
     * */
    protected static $dependencies  = array();

    /**
     * Css
     *
     * @var array
     * */
    protected static $css = array();

    /**
     * Directorio Css
     *
     * @var array
     * */
    protected static $css_dir = 'css/';

    /**
     * Añade un archivo Css fuera del template para ser incluido en el template
     *
     * @param string $file nombre del archivo a añadir
     * @param array $dependencies  archivos que son requisito del archivo a añadir
     */
    public static function add($file, array $dependenciess = [])
    {
        self::$css[$file] = $file;
        foreach ($dependenciess as $file) {
            self::$dependencies[$file] = $file;
        }
    }

    /**
     * Incluye todos los archivo Css en el template añadidos con el metodo add
     *
     * @return string
     */
    public static function inc()
    {
        $csss = self::$dependencies  + self::$css;
        $html = '';
        foreach ($csss as $file) {
            $html .= '<link href="'.PUBLIC_PATH.self::$css_dir.$file.'.css" rel="stylesheet" type="text/css" />'
            .PHP_EOL;
        }
        return $html;
    }
}
