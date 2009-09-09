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
 * Helper para Form
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

class Form extends Tag
{
    /**
     * Utilizado para generar los id de los radio button,
     * lleva un conteo interno
     *
     * @var array
     **/
    protected static $_radios = array();

    /**
     * Obtiene el nombre de formulario y campo
     *
     * @param string $name nombre de campo con formato model.field
     * @return array
     **/
    public static function getFormField($name)
    {
        $buff = explode('.', $name, 2);
        if(isset($buff[1])) {
            $data['form'] = $buff[0];
            $data['field'] = $buff[1]; 
        } else {
            $data['form'] = null;
            $data['field'] = $buff[0];
        }
        return $data;
    }
    /**
     * Genera un string con atributos id y name 
     *
     * @param array $field
     * @param boolean $radio indica si es radio button
     * @return string
     **/
    public static function getIdAndName($field, $radio=false)
    {
        if($field['form']) {
            $id = "{$field['form']}_{$field['field']}";
            $name = "{$field['form']}[{$field['field']}]";
        } else {
            $id = $name = $field['field'];
        }
        
        if($radio) {
            if(isset(self::$_radios[$name])) {
                self::$_radios[$name]++;
            } else {
                self::$_radios[$name] = 0;
            }
            $id .= self::$_radios[$name];
        }
        
        return " id=\"$id\" name=\"$name\"";
    }
    /**
     * Obtiene el valor de un componente tomado
     * del mismo valor del nombre del campo y formulario
     * que corresponda a un atributo del mismo nombre
     * que sea un string, objeto o array.
     *
     * @param array $field
     * @return mixed
     */
    public static function getValueFromAction ($field)
    {
        $form = $field['form'];
        $field = $field['field'];
        
        // obtiene el controller
        $controller = Dispatcher::get_controller();
        
        $value = null;
        
        // si es formato especial para formulario y se ha pasado dato por el controller
        if ($form && isset($controller->$form)) {
            $v = $controller->$form;
            if (is_object($v) && isset($v->$field)) {
                $value = $v->$field;
            } elseif (is_array($v) && isset($v[$field])) {
                $value = $v[$field];
            }
        } elseif (isset($controller->$field)) { // verifica si el usuario lo ha pasado por el como campo simple
            $value = $controller->$field;
        }
        
        // filtrar caracteres especiales
        if($value) {
            $value = htmlspecialchars($value, ENT_COMPAT, APP_CHARSET);
        }
        
        return $value;
    }
    /**
     * Crea campo input
     *
     * @param string $content contenido interno
     * @param string $attrs atributos para el tag
     * @return string
     **/
    public static function input ($content = null, $attrs = null)
    {
        if(is_array($attrs)) { 
            $attrs = self::getAttrs($attrs); 
        }
        if (is_null($content)) {
            return "<input $attrs/>";
        }
        echo "<input $attrs>$content</input>";
    }
    /**
     * Crea una etiqueta de formulario
     *
     * @param string $action
     * @param string $method
     * @param array $attrs
     * @return Html
     */
    public static function open ($action = null, $method = 'post', $attrs = null)
    {
        if (is_array($attrs)) {
            $attrs = self::getAttrs($attrs);
        }
        if ($action) {
            $action = URL_PATH . $action;
        } else {
            $action = URL_PATH . substr(Router::get('route'), 1);
        }
        echo "<form action=\"$action\" method=\"$method\" $attrs>";
    }
    
    /**
     * Crea una etiqueta de formulario multipart
     *
     * @param string $action
     * @param array $attrs
     * @return Html
     */
    public static function openMultipart($action = null, $attrs = null)
    {
        if (is_array($attrs)) {
            $attrs = self::getAttrs($attrs);
        }
        if ($action) {
            $action = URL_PATH . $action;
        } else {
            $action = URL_PATH . substr(Router::get('route'), 1);
        }
        echo "<form action=\"$action\" method=\"post\" enctype=\"multipart/form-data\" $attrs>";
    }
    
    /**
     * Etiqueta para cerrar un formulario
     *
     * @return string
     */
    public static function close ()
    {
        echo '</form>';
    }
    /**
     * Crea un boton de submit para el formulario actual
     *
     * @param string $text
     * @param array $attrs
     * @return string
     */
    public static function submit ($text, $attrs = null)
    {
        if (is_array($attrs)) {
            $attrs = self::getAttrs($attrs);
        }
        echo "<input type=\"submit\" value=\"$text\" $attrs />";
    }
    /**
     * Crea un boton reset
     *
     * @param string $text
     * @param array $attrs
     * @return string
     */
    public static function reset ($text, $attrs = null)
    {
        if (is_array($attrs)) {
            $attrs = self::getAttrs($attrs);
        }
        echo "<input type=\"reset\" value=\"$text\" $attrs />";
    }
    /**
     * Crea un boton
     *
     * @param string $text
     * @param array $attrs
     * @return string
     */
    public static function button ($text, $attrs = null)
    {
        if (is_array($attrs)) {
            $attrs = self::getAttrs($attrs);
        }
        echo "<input type=\"button\" value=\"$text\" $attrs />";
    }
        
    /**
     * Campo text
     *
     * @param string $name nombre de campo
     * @param string|array $attrs atributos de campo
     * @param string $value
     **/
    public static function text($name, $attrs=null, $value=null)
    {
        if(is_array($attrs)) {
            $attrs = self::getAttrs($attrs);
        }
        
        $field = self::getFormField($name);
        $id_name = self::getIdAndName($field);
        
        if(is_null($value)) {
            $value = self::getValueFromAction($field);
        }
        
        echo "<input $id_name type=\"text\" value=\"$value\" $attrs/>";
    }
    
    /**
     * Campo Select
     *
     * @param string $name nombre de campo
     * @param string $data array de valores para la lista desplegable
     * @param string|array $attrs atributos de campo
     * @param string $value
     **/
    public static function select($name, $data, $attrs=null, $value=null)
    {
        if(is_array($attrs)){
            $attrs = self::getAttrs($attrs);
        }
        
        $field = self::getFormField($name);
        $id_name = self::getIdAndName($field);
        if(is_null($value)) {
            $value = self::getValueFromAction($field);
        }
        
        $options = '';
        foreach($data as $k => $v) {
            $k = htmlspecialchars($k, ENT_COMPAT, APP_CHARSET);
            $options .= "<option value=\"$k\"";
            if($k == $value) {
                $options .= ' selected="selected"';
            }
            $options .= '>' . htmlspecialchars($v, ENT_COMPAT, APP_CHARSET) . '</option>';
        }
        
        echo "<select $id_name $attrs>$options</select>";
    }
    
    /**
     * Campo checkbox
     *
     * @param string $name nombre de campo
     * @param string $value valor en el checkbox
     * @param string|array $attrs atributos de campo
     * @param string $checked indica si se marca el campo
     **/
    public static function check($name, $value, $attrs=null, $checked=null)
    {
        if(is_array($attrs)) {
            $attrs = self::getAttrs($attrs);
        }
        
        $field = self::getFormField($name);
        $id_name = self::getIdAndName($field);
        
        if(is_null($checked)) {
            $checked = self::getValueFromAction($field) == $value;
        }
        
        if($checked) {
            $checked = 'checked="checked"';
        }
        
        echo "<input $id_name type=\"checkbox\" value=\"$value\" $attrs $checked/>";
    }
    
    /**
     * Campo radio button
     *
     * @param string $name nombre de campo
     * @param string $value valor en el radio
     * @param string|array $attrs atributos de campo
     * @param string $checked indica si se marca el campo
     **/
    public static function radio ($name, $value, $attrs=null, $checked=null)
    {
        if(is_array($attrs)){
            $attrs = self::getAttrs($attrs);
        }
        
        $field = self::getFormField($name);
        $id_name = self::getIdAndName($field, true);
        
        if(is_null($checked)) {
            $checked = self::getValueFromAction($field) == $value;
        }
        
        if($checked) {
            $checked = 'checked="checked"';
        }
        
        echo "<input $id_name type=\"radio\" value=\"$value\" $attrs $checked/>";
    }
    
    /**
     * Crea un boton de tipo imagen
     *  
     * @param string $img
     * @param array $attrs
     * @return string
     */
    public static function submitImage ($img, $attrs = null)
    {
        if (is_array($attrs)) {
            $attrs = self::getAttrs($attrs);
        }
        echo "<input type=\"image\" src=\"".PUBLIC_PATH."img/$img\" $attrs/>";
    }
    
    /**
     * Campo hidden
     *
     * @param string $name nombre de campo
     * @param string|array $attrs atributos de campo
     * @param string $value
     **/
    public static function hidden ($name, $attrs=null, $value=null)
    {
        if(is_array($attrs)) {
            $attrs = self::getAttrs($attrs);
        }
        
        $field = self::getFormField($name);
        $id_name = self::getIdAndName($field);
        
        if(is_null($value)) {
            $value = self::getValueFromAction($field);
        }
        
        echo "<input $id_name type=\"hidden\" value=\"$value\" $attrs/>";
    }
    
    /**
     * Campo Password
     *
     * @param string $name nombre de campo
     * @param string|array $attrs atributos de campo
     * @param string $value
     **/
    public static function pass($name, $attrs=null, $value=null)
    {
        if(is_array($attrs)) {
            $attrs = self::getAttrs($attrs);
        }
        
        $field = self::getFormField($name);
        $id_name = self::getIdAndName($field);
        
        if(is_null($value)) {
            $value = self::getValueFromAction($field);
        }
        
        echo "<input $id_name type=\"password\" value=\"$value\" $attrs/>";
    }
    
    /**
     * Campo Select que toma los valores de un array de objetos
     *
     * @param string $name nombre de campo
     * @param string $data array de valores para la lista desplegable
     * @param string $field campo que se mostrara
     * @param string|array $attrs atributos de campo
     * @param string $value
     **/
    public static function dbSelect($name, $data, $field, $attrs=null, $value=null)
    {
        if(is_array($attrs)) {
            $attrs = self::getAttrs($attrs);
        }
        
        $field_data = self::getFormField($name);
        $id_name = self::getIdAndName($field_data);
        if(is_null($value)) {
            $value = self::getValueFromAction($field_data);
        }
        
        $options = '';
        foreach($data as $p) {
            $options .= "<option value=\"$p->id\"";
            if($p->id == $value) {
                $options .= ' selected="selected"';
            }
            $options .= '>' . htmlspecialchars($p->$field, ENT_COMPAT, APP_CHARSET) . '</option>';
        }
        
        echo "<select $id_name $attrs>$options</select>";
    }
    
    /**
     * Campo File
     *
     * @param string $name nombre de campo
     * @param string|array $attrs atributos de campo
     **/
    public static function file($name, $attrs=null)
    {
        if(is_array($attrs)) {
            $attrs = self::getAttrs($attrs);
        }
        
        $field = self::getFormField($name);
        $id_name = self::getIdAndName($field);
        
        echo "<input $id_name type=\"file\" $attrs/>";
    }

    /**
     * Campo textarea
     *
     * @param string $name nombre de campo
     * @param string|array $attrs atributos de campo
     * @param string $value
     **/
    public static function textarea($name, $attrs=null, $value=null)
    {
        if(is_array($attrs)) {
            $attrs = self::getAttrs($attrs);
        }
        
        $field = self::getFormField($name);
        $id_name = self::getIdAndName($field);
        
        if(is_null($value)) {
            $value = self::getValueFromAction($field);
        }
        
        echo "<textarea $id_name $attrs>$value</textarea>";
    }
}