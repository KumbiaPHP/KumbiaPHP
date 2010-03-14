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
     * @param string | array $attrs atributos adicionales
     * @return string
     */
    public static function link ($action, $text, $confirm, $class=NULL, $attrs=NULL)
    {
        if (is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }
        return '<a href="' . PUBLIC_PATH . "$action\" title=\"$confirm\" class=\"js-confirm $class\" $attrs>$text</a>";
    }
   
	/**
     * Crea un enlace en una Aplicacion con mensaje de confirmacion respetando
     * las convenciones de Kumbia
     *
     * @param string $text texto a mostrar
     * @param string $confirm mensaje de confirmacion
     * @param string $class clases adicionales para el link
     * @param string | array $attrs atributos adicionales
     * @return string
     */
    public static function submit ($text, $confirm, $class=NULL, $attrs=NULL)
    {
        if (is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }
        return "<input type=\"submit\" value=\"$text\" title=\"$confirm\" class=\"js-confirm $class\" $attrs/>";
    }

    /**
     * Campo para calendario
     *
     * @param string $field nombre de campo
     * @param string $format formato de fecha como lo acepta jsCalendar
     * @param string $class clases adicionales
     * @param string $attrs atributos de campo
     * @param string $value valor para el campo
     * @return string
     **/
    public static function calendar($field, $format='%d-%m-%Y', $class=null, $attrs=null, $value=null)
    {
        if(is_array($attrs)){
            $attrs = Tag::getAttrs($attrs);
        }

        return Form::text($field, "class=\"js-calendar $class\" $attrs", $value) . ' ' . Html::img('calendar.gif', $format, "id=\"$field.tigger\"");
    }
    
	
	/**
	 * Incluye las librerias basicas para funcionamiento de jQuery con KumbiaPHP
	 * 
	 * @return string
	 */
	public static function includeJQuery()
	{
		return Tag::js('jquery/jquery.min') . PHP_EOL . Tag::js('jquery/jquery.kumbiaphp');
	}
	
	/**
	 * Incluye las librerias para uso de jsCalendar
	 * 
	 * @param string $theme tema
	 * @param string $language idioma
	 * @return string
	 */
	public static function includeJsCalendar($theme = 'theme-1', $language = 'es')
	{
		// incluye el tema
		Tag::css("style-calendar/$theme");
		
		// incluye los javascript
		return Tag::js('kumbia/jscalendar/calendar') . PHP_EOL
			. Tag::js('kumbia/jscalendar/calendar-setup') . PHP_EOL
			. Tag::js("kumbia/jscalendar/calendar-$language");
	}
}
