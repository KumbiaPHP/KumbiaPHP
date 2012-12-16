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
 * @copyright  Copyright (c) 2005-2012 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
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
    public static function link($action, $text, $confirm = '¿Está Seguro?', $class = NULL, $attrs = NULL)
    {
        if (is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }
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
    public static function linkAction($action, $text, $confirm = '¿Está Seguro?', $class = NULL, $attrs = NULL)
    {
        if (is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }
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
    public static function submit($text, $confirm = '¿Está Seguro?', $class = NULL, $attrs = NULL)
    {
        if (is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }
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
    public static function submitImage($img, $confirm = '¿Está Seguro?', $class = NULL, $attrs = NULL)
    {
        if (is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }
        return "<input type=\"image\" data-msg=\"$confirm\" src=\"" . PUBLIC_PATH . "img/$img\" class=\"js-confirm $class\" $attrs/>";
    }

}
