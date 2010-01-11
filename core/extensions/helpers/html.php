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
 * Helper para Tags Html
 * 
 * @category   KumbiaPHP
 * @package    Helpers 
 * @copyright  Copyright (c) 2005-2009 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */
 
/**
 * @see Tag
 **/


class Html
{
    /**
     * Alternador para tabla zebra
     *
     * @var boolean
     **/
    protected static $_trClassAlternate = TRUE;

    /**
     * Metatags
     *
     * @var array
     **/
    protected static $_metatags = array();

    /**
     * Crea un enlace en una Aplicacion respetando
     * las convenciones de Kumbia
     *
     * @param string $action ruta a la accion
     * @param string $text texto a mostrar
     * @param string | array $attrs atributos adicionales
     * @return string
     */
    public static function link ($action, $text, $attrs = NULL)
    {
        if (is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }
        return '<a href="' . URL_PATH . "$action\" $attrs>$text</a>";
    }
    /**
     * Permite incluir una imagen
     *
     * @param string $src
     * @params string $alt
     * @param string | array $attrs atributos adicionales
     */
    public static function img ($src, $alt=NULL, $attrs = NULL)
    {
        if (is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }
        return '<img src="' . PUBLIC_PATH . "img/$src\" alt=\"$alt\" $attrs/>";
    }
    
    /**
     * Aplica estilo zebra a una tabla.
     *
     * @param string $class class css
     * @param string | array $attrs
     * @param unknown_type $start
     */
    public static function trClass ($class, $attrs = NULL)
    {
        if (is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }
        if(self::$_trClassAlternate){
            self::$_trClassAlternate = FALSE;
            return "<tr class='$class' $attrs>";
        } else {
            self::$_trClassAlternate = TRUE;
            return "<tr $attrs>";
        }
    }
    
    /**
     * Inicia el alternador de clase para tabla zebra
     *
     */
    public static function trClassStart ()
    {
        self::$_trClassAlternate = TRUE;
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
