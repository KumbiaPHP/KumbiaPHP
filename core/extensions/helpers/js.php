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
 * @copyright  Copyright (c) 2005 - 2020 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Helper que utiliza Javascript
 *
 * @category   KumbiaPHP
 * @package    Helpers
 */
class Js
{
    /**
     * Javascripts que son requisito de otros
     *
     * @var array
     * */
    protected static array $_dependencies = [];
    
    /**
     * Javascript
     *
     * @var array
     * */
    protected static array $_js = [];
    
    /**
     * Directorio Javascript
     *
     * @var string
     * */
    protected static string $js_dir = 'javascript/';

    /**
     * Crea un enlace en una Aplicacion con mensaje de confirmacion respetando
     * las convenciones de Kumbia
     *
     * @param string $action ruta a la accion
     * @param string $text texto a mostrar
     * @param string $confirm mensaje de confirmacion
     * @param string $class clases adicionales para el link
     * @param string|array $attrs atributos adicionales
     * @return string
     */
    public static function link(string $action, string $text, string $confirm = '¿Está Seguro?', string $class = '', $attrs = ''): string
    {
        $attrs = Tag::getAttrs($attrs);
        return '<a href="' . PUBLIC_PATH . "$action\" data-msg=\"$confirm\" class=\"js-confirm $class\" $attrs>$text</a>";
    }

    /**
     * Crea un enlace a una accion con mensaje de confirmacion respetando
     * las convenciones de Kumbia
     *
     * @param string $action accion
     * @param string $text texto a mostrar
     * @param string $confirm mensaje de confirmacion
     * @param string $class clases adicionales para el link
     * @param string|array $attrs atributos adicionales
     * @return string
     */
    public static function linkAction(string $action, string $text, string $confirm = '¿Está Seguro?', string $class = '', $attrs = ''): string
    {
        $attrs = Tag::getAttrs($attrs);
        return '<a href="' . PUBLIC_PATH . Router::get('controller_path') . "/$action\" data-msg=\"$confirm\" class=\"js-confirm $class\" $attrs>$text</a>";
    }

    /**
     * Crea un boton submit con mensaje de confirmacion respetando
     * las convenciones de Kumbia
     *
     * @param string $text texto a mostrar
     * @param string $confirm mensaje de confirmacion
     * @param string $class clases adicionales para el link
     * @param string|array $attrs atributos adicionales
     * @return string
     */
    public static function submit(string $text, string $confirm = '¿Está Seguro?', string $class = '', $attrs = ''): string
    {
        $attrs = Tag::getAttrs($attrs);
        return "<input type=\"submit\" value=\"$text\" data-msg=\"$confirm\" class=\"js-confirm $class\" $attrs/>";
    }

    /**
     * Crea un boton de tipo imagen
     *
     * @param string $img
     * @param string $class clases adicionales para el link
     * @param string|array $attrs atributos adicionales
     * @return string
     */
    public static function submitImage(string $img, string $confirm = '¿Está Seguro?', $class = '', $attrs = ''): string
    {
        $attrs = Tag::getAttrs($attrs);
        return "<input type=\"image\" data-msg=\"$confirm\" src=\"" . PUBLIC_PATH . "img/$img\" class=\"js-confirm $class\" $attrs/>";
    }

    /**
     * Añade un archivo Javascript para ser incluido en el template
     *
     * @param string $file nombre del archivo a añadir
     * @param array $dependencies archivos que son requisito del archivo a añadir
     */
    public static function add( string $file, array $dependencies = [] ): void
    {
        self::$_js[$file] = $file;
        foreach ($dependencies as $file) {
            self::$_dependencies[$file] = $file;
        }
    }
    
    /**
     * Incluye todos los archivo Javascript en el template añadidos con el metodo add
     *
     * @return string
     */
    public static function inc(): string
    {
        $js = self::$_dependencies + self::$_js;
        $html = '';
        foreach ($js as $file)
        {
            $html .= '<script type="text/javascript" src="' . PUBLIC_PATH . self::$js_dir . "$file.js" . '"></script>';
        }
        return $html;
    }
}
