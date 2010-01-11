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

 
class Js
{
    /**
     * Metadata
     *
     * @var array
     **/
    protected static $_metadata = array();

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
        return '<a href="' . URL_PATH . "$action\" title=\"$confirm\" class=\"js-confirm $class\" $attrs>$text</a>";
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
    public static function linkRemote ($action, $text, $update, $class=NULL, $attrs=NULL)
    {
        if (is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }
        return '<a href="' . URL_PATH . "$action\" class=\"js-remote $class\" rel=\"#{$update}\" $attrs>$text</a>";
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
    public static function linkRemoteConfirm ($action, $text, $update, $confirm, $class=NULL, $attrs=NULL)
    {
        if (is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }
        return '<a href="' . URL_PATH . "$action\" class=\"js-remote-confirm $class\" rel=\"#{$update}\" title=\"$confirm\" $attrs>$text</a>";
    }

    /**
     * Campo para calendario
     *
     * @param string $name nombre de campo
     * @param string $format formato de fecha como lo acepta jsCalendar
     * @param string $class clases adicionales
     * @param string $attrs atributos de campo
     * @param string $value valor para el campo
     * @return string
     **/
    public static function calendar($name, $format='%d-%m-%Y', $class=null, $attrs=null, $value=null)
    {
        if(is_array($attrs)){
            $attrs = Tag::getAttrs($attrs);
        }
    
        $field = Form::getFormField($name);
        if($field['form']) {
            $id = "{$field['form']}_{$field['field']}";
        } else {
            $id = $field['field'];
        }
        return Form::text($name, "class=\"js-calendar $class\" $attrs", $value) . ' ' . Html::img('calendar.gif', $format, "id=\"{$id}_tigger\"");
    }
    
    /**
     * Lista desplegable para actualizar usando ajax
     *
     * @param string $name nombre de campo
     * @param array $data
     * @param string $update capa que se actualizara
     * @param string $action accion que se ejecutara
     * @param string $class
     * @param string | array $attrs
     **/
    public static function updaterSelect($name, $data, $update, $action, $class=null, $attrs=null)
    {
        if(is_array($attrs)){
            $attrs = Tag::getAttrs($attrs);
        }
        
        $field = Form::getFormField($name);
        if($field['form']) {
            $id = "{$field['form']}_{$field['field']}";
        } else {
            $id = $field['field'];
        }
        
        // asigna la metadata (la accion a ejecutar y la capa que se actualizara)
        self::$_metadata[$id] = array(
            'action' => URL_PATH . rtrim($action, '/') . '/',
            'update' => $update
        );
		
        // genera el campo
        return Form::select($name, $data, "class=\"js-remote $class\" $attrs");
    }
	
    /**
     * Lista desplegable para actualizar usando ajax que toma los valores de un array de objetos
     *
     * @param string $name nombre de campo
     * @param array $data
     * @param string $field campo que se mostrara
     * @param string $update capa que se actualizara
     * @param string $action accion que se ejecutara
     * @param string $blank campo en blanco
     * @param string $class
     * @param string | array $attrs
     **/
    public static function updaterDbSelect($name, $data, $field, $update, $action, $blank=null, $class=null, $attrs=null)
    {
        if(is_array($attrs)){
            $attrs = Tag::getAttrs($attrs);
        }
        
        $field = Form::getFormField($name);
        if($field['form']) {
            $id = "{$field['form']}_{$field['field']}";
        } else {
            $id = $field['field'];
        }
        
        // asigna la metadata (la accion a ejecutar y la capa que se actualizara)
        self::$_metadata[$id] = array(
            'action' => URL_PATH . rtrim($action, '/') . '/',
            'update' => $update
        );
        
        // genera el campo
        return Form::dbSelect($name, $data, $field, $blank, "class=\"js-remote $class\" $attrs");
    }
    
    /**
     * Asigna una metadata
     *
     * @param string $name nombre de metadata
     * @param mixed $value valor
     **/
    public static function setMetadata($name, $value)
    {
        self::$_metadata[$name] = $value;
    }
    
    /**
     * Obtiene una metadata asignada
     *
     * @param string $name nombre de metadata
     * @return mixed
     **/
    public static function getMetadata($name)
    {
        if(isset(self::$_metadata[$name])) {
            return self::$_metadata[$name];
        } else {
            return null;
        }
    }
    
    /**
     * Incluye la metadata para javascript
     *
     * @return string
     **/
    public static function includeMetadata()
    {
        return '<script type="text/javascript"> jQuery.KumbiaPHP.metadata = ' . json_encode(self::$_metadata) . '; </script>';
    }
}