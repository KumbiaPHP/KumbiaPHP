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
        if ($attrs) {
            $attrs = self::getAttrs($attrs);
        }
        return '<a href="' . URL_PATH . "$action\" $attrs>$text</a>";
    }
    /**
     * Permite incluir una imagen
     *
     * @param string $src
     * @param string | array $attrs atributos adicionales
     */
    public static function img ($src, $attrs = null)
    {
        if ($attrs) {
            $attrs = self::getAttrs($attrs);
        }
        return '<img src="' . PUBLIC_PATH . "img/$src\" $attrs/>";
    }
}