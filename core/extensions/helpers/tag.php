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
 * Helper Tag
 *
 * Helper base para creacion de Tags
 *
 * @category   KumbiaPHP
 * @package    Helpers
 * @copyright  Copyright (c) 2005-2009 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */
class Tag
{
    /**
     * Recursos enlazados
     *
     * @var array
     **/
    protected static $_links = array();

    /**
     * Metatags
     *
     * @var array
     **/
    protected static $_metatags = array();
    
    /**
     * Convierte los argumentos de un metodo de parametros por nombre a un string con los atributos
     *
     * @param array $params argumentos a convertir
     * @return string
     */
    public static function getAttrs($params)
    {
        $data = '';
        foreach($params as $k => $v) {
            $data .= " $k=\"$v\"";
        }
        return $data;
    }
    
    /**
     * Crea un tag
     *
     * @param string $tag nombre de tag
     * @param string $content contenido interno
     * @param string $attrs atributos para el tag
     * @return string
     **/
    public static function create($tag, $content = NULL, $attrs = NULL) 
    {
        if(is_array($attrs)) {
            $attrs = self::getAttrs($attrs);
        }

        if(is_null($content)) {
            echo "<$tag $attrs />";
        }

        echo "<$tag $attrs>$content</$tag>";
    }
    
    /**
     * Incluye un archivo javascript
     *
     * @param string $src archivo javascript
     * @param boolean $cache indica si se usa cache de navegador
     */
    public static function js($src, $cache=TRUE)
    {
        $code = '';
        $src = "javascript/$src.js";
        if(!$cache) {
            $src .= '?nocache=' . uniqid();
        }
        $code .= '<script type="text/javascript" src="' . PUBLIC_PATH . $src . '"></script>';
        echo $code;
    }
    
    /**
     * Incluye un archivo de css
     *
     * @param string $src archivo css
     * @param string $media medio de la hoja de estilo
     */
    public static function css($src, $media='screen')
    {
        self::$_links[] = '<link href="' . PUBLIC_PATH . "css/$src.css\" rel=\"stylesheet\" type=\"text/css\" media=\"$media\"/>";
    }
    
    /**
     * Enlaza un recurso externo
     *
     * @param string $href direccion url del recurso a enlazar
     * @param string|array $attrs atributos
     */
    public static function link($href, $attrs=null)
    {
        if(is_array($attrs)) {
            $attrs = self::getAttrs($attrs);
        }
    
        self::$_links[] = "<link href=\"$href\" $attrs/>";
    }
    
    /**
     * Enlaza una accion
     *
     * @param string $url direccion url del recurso a enlazar
     * @param string|array $attrs atributos
     */
    public static function linkAction($action, $attrs=null)
    {
        self::link(URL_PATH . $action, $attrs);
    }
    
    /**
     * Enlaza un recurso de la aplicacion
     *
     * @param string $url direccion url del recurso a enlazar
     * @param string|array $attrs atributos
     */
    public static function linkResource($resource, $attrs=null)
    {
        self::link(PUBLIC_PATH . $resource, $attrs);
    }
    
    /**
     * Incluye los recursos enlazados
     *
     * @return string
     **/
    public static function includeLinks()
    {
        return implode(array_unique(self::$_links), PHP_EOL);
    }
    
    /**
     * Crea un metatag
     *
     * @param string $content contenido del metatag
     * @param string|array $attrs atributos
     */
    public static function meta($content, $attrs=null)
    {
        if(is_array($attrs)) {
            $attrs = self::getAttrs($attrs);
        }
    
        self::$_metatags[] = "<meta content=\"$content\" $attrs/>";
    }
    
    /**
     * Incluye los metatags
     *
     * @return string
     **/
    public static function includeMetatags()
    {
        return implode(array_unique(self::$_metatags), PHP_EOL);
    }
}
