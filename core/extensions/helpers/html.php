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
require_once CORE_PATH . 'extensions/helpers/tag.php';

class Html extends Tag
{
    /**
     * Crea un enlace en una Aplicacion respetando
     * las convenciones de Kumbia
     *
     * @param string $action ruta a la accion
     * @param string $text texto a mostrar
     * @param string | array $attrs atributos adicionales
     * @return string
     */
    public static function link ($action, $text, $attrs = null)
    {
        if (is_array($attrs)) {
            $attrs = self::getAttrs($attrs);
        }
        echo '<a href="' . URL_PATH . "$action\" $attrs>$text</a>";
    }
    /**
     * Permite incluir una imagen
     *
     * @param string $src
     * @param string | array $attrs atributos adicionales
     */
    public static function img ($src, $attrs = null)
    {
        if (is_array($attrs)) {
            $attrs = self::getAttrs($attrs);
        }
        echo '<img src="' . PUBLIC_PATH . "img/$src\" $attrs/>";
    }
    /**
     * Crea un link con imagen
     *
     * @param string $action ruta a la accion
     * @param string $src
     * @param string | array $attrslink atributos adicionales del link
     * @param string | array $attrsImg atributos adicionales de la imagen
     * @return unknown
     */
    public static function linkImg ($action, $src, $attrsLink=null, $attrsImg = null)
    {
        if (is_array($attrsLink)) {
            $attrsLink = self::getAttrs($attrsLink);
        }
        if(is_array($attrsImg)){
            $attrsImg = self::getAttrs($attrsImg);
        }
        echo "<a href=\"" . URL_PATH . "$action\" $attrsLink><img src=\"" . PUBLIC_PATH ."img/$src\" $attrsImg /></a>";
    }
    /**
     * Aplica estilo zebra a una tabla.
     *
     * @param string $class class css
     * @param string | array $attrs
     * @param unknown_type $start
     */
    public static function trClass ($class, $attrs = null)
    {
        static $c = true;
        if (is_array($attrs)) {
            $attrs = self::getAttrs($attrs);
        }
        if($c){
            echo "<tr class='$class' $attrs>";
            $c = false;
        } else {
            echo "<tr $attrs>";
            $c = true;
        }
    }
    
    /**
     * Aplica estilo zebra a una tabla. Aplica para cuando hay dos tabla en el mismo XHTML
     *
     * @param string $class class css
     * @param string | array $attrs
     * @param unknown_type $start
     */
    public static function trClassStart ($class, $attrs = null)
    {
        static $c = true;
        if (is_array($attrs)) {
            $attrs = self::getAttrs($attrs);
        }
        if($c){
            echo "<tr class='$class' $attrs>";
            $c = false;
        } else {
            echo "<tr $attrs>";
            $c = true;
        }
    }
}