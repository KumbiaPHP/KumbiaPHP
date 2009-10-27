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
 * Helper que utiliza Javascript
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
 
class Js extends Tag
{
    /**
     * Crea un enlace en una Aplicacion con mensaje de confirmacion respetando
     * las convenciones de Kumbia
     *
     * @param string $action ruta a la accion
     * @param string $text texto a mostrar
     * @param string $confirm mensaje de confirmacion
     * @param string $class clases adicionales para el link
     * @param string | array $attrs atributos adicionales
     * @return string
     */
    public static function link ($action, $text, $confirm, $class=null, $attrs=null)
    {
        if (is_array($attrs)) {
            $attrs = self::getAttrs($attrs);
        }
        return '<a href="' . URL_PATH . "$action\" title=\"$confirm\" class=\"js-confirm $class\" $attrs>$text</a>";
    }
    /**
     * Crea un link con imagen con mensaje de confirmacion
     *
     * @param string $action ruta a la accion
     * @param string $src
     * @param string $confirm mensaje de confirmacion
     * @param string $alt
     * @param string $class clases adicionales para el link
     * @param string | array $attrslink atributos adicionales del link
     * @param string | array $attrsImg atributos adicionales de la imagen
     * @return unknown
     */
    public static function linkImg ($action, $src, $confirm, $alt=null, $class=null, $attrsLink=null, $attrsImg=null)
    {
        if (is_array($attrsLink)) {
            $attrsLink = self::getAttrs($attrsLink);
        }
        if(is_array($attrsImg)){
            $attrsImg = self::getAttrs($attrsImg);
        }
        return "<a href=\"" . URL_PATH . "$action\" title=\"$confirm\" class=\"js-confirm $class\" $attrsLink><img src=\"" . PUBLIC_PATH ."img/$src\" alt=\"$alt\" $attrsImg /></a>";
    }
    /**
     * Crea un enlace en una Aplicacion actualizando la capa con ajax
     *
     * @param string $action ruta a la accion
     * @param string $text texto a mostrar
     * @param string $update capa a actualizar
     * @param string $class clases adicionales
     * @param string | array $attrs atributos adicionales
     * @return string
     */
    public static function linkRemote ($action, $text, $update, $class=null, $attrs=null)
    {
        if (is_array($attrs)) {
            $attrs = self::getAttrs($attrs);
        }
        return '<a href="' . URL_PATH . "$action\" class=\"js-remote $class\" rel=\"#{$update}\" $attrs>$text</a>";
    }
    /**
     * Crea un link con imagen y actualiza la capa con ajax
     *
     * @param string $action ruta a la accion
     * @param string $src
     * @param string $update capa a actualizar
     * @param string $alt
     * @param string $class clases adicionales para el link
     * @param string | array $attrslink atributos adicionales del link
     * @param string | array $attrsImg atributos adicionales de la imagen
     * @return unknown
     */
    public static function linkImgRemote ($action, $src, $update, $alt=null, $class=null, $attrsLink=null, $attrsImg=null)
    {
        if (is_array($attrsLink)) {
            $attrsLink = self::getAttrs($attrsLink);
        }
        if(is_array($attrsImg)){
            $attrsImg = self::getAttrs($attrsImg);
        }
        return "<a href=\"" . URL_PATH . "$action\" class=\"js-remote $class\" rel=\"#{$update}\" $attrsLink><img src=\"" . PUBLIC_PATH ."img/$src\" alt=\"$alt\" $attrsImg /></a>";
    }
    /**
     * Crea un enlace en una Aplicacion actualizando la capa con ajax con mensaje
     * de confirmacion
     *
     * @param string $action ruta a la accion
     * @param string $text texto a mostrar
     * @param string $update capa a actualizar
     * @param string $confirm mensaje de confirmacion
     * @param string $class clases adicionales
     * @param string | array $attrs atributos adicionales
     * @return string
     */
    public static function linkRemoteConfirm ($action, $text, $update, $confirm, $class=null, $attrs=null)
    {
        if (is_array($attrs)) {
            $attrs = self::getAttrs($attrs);
        }
        return '<a href="' . URL_PATH . "$action\" class=\"js-remote-confirm $class\" rel=\"#{$update}\" title=\"$confirm\" $attrs>$text</a>";
    }
    /**
     * Crea un link con imagen y actualiza la capa con ajax, e incluye mensaje de confirmacion
     *
     * @param string $action ruta a la accion
     * @param string $src
     * @param string $update capa a actualizar
     * @param string $confirm mensaje de confirmacion
     * @param string $alt
     * @param string $class clases adicionales para el link
     * @param string | array $attrslink atributos adicionales del link
     * @param string | array $attrsImg atributos adicionales de la imagen
     * @return unknown
     */
    public static function linkImgRemoteConfirm ($action, $src, $update, $confirm, $alt=null, $class=null, $attrsLink=null, $attrsImg=null)
    {
        if (is_array($attrsLink)) {
            $attrsLink = self::getAttrs($attrsLink);
        }
        if(is_array($attrsImg)){
            $attrsImg = self::getAttrs($attrsImg);
        }
        return "<a href=\"" . URL_PATH . "$action\" class=\"js-remote-confirm $class\" rel=\"#{$update}\" title=\"$confirm\" $attrsLink><img src=\"" . PUBLIC_PATH ."img/$src\" alt=\"$alt\" $attrsImg /></a>";
    }
}