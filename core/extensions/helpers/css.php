<?php
/**
 * KumbiaPHP web & app Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @category   KumbiaPHP
 * @package    Helpers
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
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
    protected static $_dependencies  = array();
    
    /**
     * Css
     *
     * @var array
     * */
    protected static $_css = array();
    
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
    public static function add( $file, array $dependencies = [] )
    {
        self::$_css[$file] = $file;
        foreach ($dependencies  as $file) self::$_dependencies [$file] = $file;
    }
    
    /**
     * Incluye todos los archivo Css en el template añadidos con el metodo add
     *
     * @return string
     */
    public static function inc()
    {
        $css = self::$_dependencies  + self::$_css;
        $html = '';
        foreach ($css as $file)
        {
            $html .= '<link href="' . PUBLIC_PATH . self::$css_dir . "$file.css\" rel=\"stylesheet\" type=\"text/css\" />" . PHP_EOL;
        }
        return $html;
    }
}
