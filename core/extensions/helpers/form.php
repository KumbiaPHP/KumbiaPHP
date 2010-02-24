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
class Form
{
    /**
     * Utilizado para generar los id de los radio button,
     * lleva un conteo interno
     *
     * @var array
     */
    protected static $_radios = array();
     
     /**
     * Utilizado para avisar al programador,si usa Form::file()
     * y no tiene el form mulipart muestra un error
     *
     * @var bool
     */
    protected static $_multipart = FALSE;

    /**
     * Obtiene el valor de un componente tomado
     * del mismo valor del nombre del campo y formulario
     * que corresponda a un atributo del mismo nombre
     * que sea un string, objeto o array.
     *
     * @param string $field
     * @return mixed
     */
    public static function getFieldData ($field)
    {
        // obtiene considerando el patron de formato form.field
        $formField = explode('.', $field, 2);
        // obtiene el controller
        $controller = Dispatcher::get_controller();
        // valor por defecto
        $value = NULL;
        
        // si tiene el formato form.field
        if(isset($formField[1])) {
            // nombre de campo
            $name = "{$formField[0]}[{$formField[1]}]";
            
            // si existe un valor cargado
            if(isset($controller->{$formField[0]})) {
                $form = $controller->{$formField[0]};
                if (is_object($form) && isset($form->{$formField[1]})) {
                    $value = $form->{$formField[1]};
                } elseif (is_array($form) && isset($form[$formField[1]])) {
                    $value = $form[$formField[1]];
                }
            }
        } else { // formato de campo comun
            // nombre de campo
            $name = $formField[0];
            
            // si existe un valor cargado
            if(isset($controller->{$formField[0]})) {
				$value = $controller->{$formField[0]};
            }
        }
        
		
        // filtrar caracteres especiales
        if($value) {
            $value = htmlspecialchars($value, ENT_COMPAT, APP_CHARSET);
        }
        
        return array('name' => $name, 'value' => $value);
    }
    
    /**
     * Obtiene el nombre de campo
     *
     * @param string $field
     * @return mixed
     */
    public static function getFieldName ($field)
    {
        // obtiene considerando el patron de formato form.field
        $formField = explode('.', $field, 2);
        
        // si tiene el formato form.field
        if(isset($formField[1])) {
            // nombre de campo
            return "{$formField[0]}[{$formField[1]}]";
        }
        
        return $field;
    }
    
    /**
     * Crea campo input
     *
     * @param string $attrs atributos para el tag
     * @param string $content contenido interno
     * @return string
     */
    public static function input ($attrs = NULL, $content = NULL)
    {
        if(is_array($attrs)) { 
            $attrs = Tag::getAttrs($attrs); 
        }
        if (is_null($content)) {
            return "<input $attrs/>";
        }
        return "<input $attrs>$content</input>";
    }
    
    /**
     * Crea una etiqueta de formulario
     *
     * @param string $action
     * @param string $method
     * @param array $attrs
     * @return string
     */
    public static function open ($action = NULL, $method = 'post', $attrs = NULL)
    {
        if (is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }
        if ($action) {
            $action = URL_PATH . $action;
        } else {
            $action = URL_PATH . substr(Router::get('route'), 1);
        }
        return "<form action=\"$action\" method=\"$method\" $attrs>";
    }
    
    /**
     * Crea una etiqueta de formulario multipart
     *
     * @param string $action
     * @param array $attrs
     * @return string
     */
    public static function openMultipart ($action = NULL, $attrs = NULL)
    {
        self::$_multipart = TRUE;
        if (is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }
        if ($action) {
            $action = URL_PATH . $action;
        } else {
            $action = URL_PATH . substr(Router::get('route'), 1);
        }
        return "<form action=\"$action\" method=\"post\" enctype=\"multipart/form-data\" $attrs>";
    }
    
    /**
     * Etiqueta para cerrar un formulario
     *
     * @return string
     */
    public static function close ()
    {
        return '</form>';
    }
    
    /**
     * Crea un boton de submit para el formulario actual
     *
     * @param string $text
     * @param array $attrs
     * @return string
     */
    public static function submit ($text, $attrs = NULL)
    {
        if (is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }
        return "<input type=\"submit\" value=\"$text\" $attrs />";
    }
    
    /**
     * Crea un boton reset
     *
     * @param string $text
     * @param array $attrs
     * @return string
     */
    public static function reset ($text, $attrs = NULL)
    {
        if (is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }
        return "<input type=\"reset\" value=\"$text\" $attrs />";
    }
    
    /**
     * Crea un boton
     *
     * @param string $text
     * @param array $attrs
     * @return string
     */
    public static function button ($text, $attrs = NULL)
    {
        if (is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }
        return "<input type=\"button\" value=\"$text\" $attrs />";
    }
	
	/**
	 * Crea un label
	 *
	 * @param string $text texto a mostrar
	 * @param string $field campo al que hace referencia
	 * @param string | array atributos opcionales
	 * @return string
	 */
	public static function label($text, $field, $attrs = NULL)
	{
		if (is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }
        return "<label for=\"$field\" $attrs>$text</label>";
	}
	
    /**
     * Campo text
     *
     * @param string $field nombre de campo
     * @param string|array $attrs atributos de campo
     * @param string $value
     * @return string
     */
    public static function text($field, $attrs = NULL, $value = NULL)
    {
        if(is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }
        
        // si no se especificó el valor explicitamente
        if($value === NULL) {
            // obtiene name y value para el campo y los carga en el scope
            extract(self::getFieldData($field), EXTR_OVERWRITE);
        } else {
            $name = self::getFieldName($field);
        }
        
        return "<input id=\"$field\" name=\"$name\" type=\"text\" value=\"$value\" $attrs/>";
    }
    
    /**
     * Campo Select
     *
     * @param string $field nombre de campo
     * @param string $data array de valores para la lista desplegable
     * @param string|array $attrs atributos de campo
     * @param string $value
     * @return string
     */
    public static function select($field, $data, $attrs = NULL, $value = NULL)
    {
        if(is_array($attrs)){
            $attrs = Tag::getAttrs($attrs);
        }
        
        // si no se especificó el valor explicitamente
        if($value === NULL) {
            // obtiene name y value para el campo y los carga en el scope
            extract(self::getFieldData($field), EXTR_OVERWRITE);
        } else {
            $name = self::getFieldName($field);
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
        
        return "<select id=\"$field\" name=\"$name\" $attrs>$options</select>";
    }
    
    /**
     * Campo checkbox
     *
     * @param string $field nombre de campo
     * @param string $value valor en el checkbox
     * @param string|array $attrs atributos de campo
     * @param string $checked indica si se marca el campo
     * @return string
     */
    public static function check($field, $value, $attrs = NULL, $checked = NULL)
    {
        if(is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }
        
        // si no se indico checked
        if($checked === NULL) {
            // obtiene name y value para el campo
            $fieldData = self::getFieldData($field);
            $name = $fieldData['name'];
            
            // verifica si debe marcarse
            if($fieldData['value'] == $value) {
                $checked = 'checked="checked"';
            }
        } else {
            $name = self::getFieldName($field);
            
            // verifica si debe marcarse
            if($checked) {
                $checked = 'checked="checked"';
            }
        }
        
        return "<input id=\"$field\" name=\"$name\" type=\"checkbox\" value=\"$value\" $attrs $checked/>";
    }
    
    /**
     * Campo radio button
     *
     * @param string $field nombre de campo
     * @param string $value valor en el radio
     * @param string|array $attrs atributos de campo
     * @param string $checked indica si se marca el campo
     * @return string
     */
    public static function radio ($field, $value, $attrs = NULL, $checked = NULL)
    {
        if(is_array($attrs)){
            $attrs = Tag::getAttrs($attrs);
        }
		
		// contador de campos radio
		if(isset(self::$_radios[$field])) {
			self::$_radios[$field]++;
		} else {
			self::$_radios[$field] = 0;
		}
		$id = $field . self::$_radios[$field];
        
        // si se marco explicitamente
        if($checked === NULL) {
            // obtiene name y value para el campo
            $fieldData = self::getFieldData($field);
            $name = $fieldData['name'];
            
            // verifica si debe marcarse
            if($fieldData['value'] == $value) {
                $checked = 'checked="checked"';
            }
        } else {
            $name = self::getFieldName($field);
            
            // verifica si debe marcarse
            if($checked) {
                $checked = 'checked="checked"';
            }
        }
        
        return "<input id=\"$id\" name=\"$name\" type=\"radio\" value=\"$value\" $attrs $checked/>";
    }
    
    /**
     * Crea un boton de tipo imagen
     *  
     * @param string $img
     * @param array $attrs
     * @return string
     */
    public static function submitImage ($img, $attrs = NULL)
    {
        if (is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }
        return "<input type=\"image\" src=\"".PUBLIC_PATH."img/$img\" $attrs/>";
    }
    
    /**
     * Campo hidden
     *
     * @param string $field nombre de campo
     * @param string|array $attrs atributos de campo
     * @param string $value
     * @return string
     */
    public static function hidden ($field, $attrs = NULL, $value = NULL)
    {
        if(is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }
        
        // si no se especificó el valor explicitamente
        if($value === NULL) {
            // obtiene name y value para el campo y los carga en el scope
            extract(self::getFieldData($field), EXTR_OVERWRITE);
        } else {
            $name = self::getFieldName($field);
        }
        
        return "<input id=\"$field\" name=\"$name\" type=\"hidden\" value=\"$value\" $attrs/>";
    }
    
    /**
     * Campo Password
     *
     * @param string $field nombre de campo
     * @param string|array $attrs atributos de campo
     * @param string $value
     */
    public static function pass($field, $attrs = NULL, $value = NULL)
    {
        if(is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }
        
        // si no se especificó el valor explicitamente
        if($value === NULL) {
            // obtiene name y value para el campo y los carga en el scope
            extract(self::getFieldData($field), EXTR_OVERWRITE);
        } else {
            $name = self::getFieldName($field);
        }
        
        return "<input id=\"$field\" name=\"$name\" type=\"password\" value=\"$value\" $attrs/>";
    }
    
    /**
     * Campo Select que toma los valores de un array de objetos
     *
     * @param string $field nombre de campo
     * @param string $data array de valores para la lista desplegable
     * @param string $show campo que se mostrara
     * @param string $blank campo en blanco
     * @param string|array $attrs atributos de campo
     * @param string $value
     * @return string
     */
    public static function dbSelect($field, $data, $show, $blank = NULL, $attrs = NULL, $value = NULL)
    {
        if(is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }
        
        // si no se especificó el valor explicitamente
        if($value === NULL) {
            // obtiene name y value para el campo y los carga en el scope
            extract(self::getFieldData($field), EXTR_OVERWRITE);
        } else {
            $name = self::getFieldName($field);
        }
        
        if(is_null($blank)) {
            $options = '';
        } else {
            $options = '<option value="">' . htmlspecialchars($blank, ENT_COMPAT, APP_CHARSET) . '</option>';
        }
        
        foreach($data as $p) {
            $options .= "<option value=\"$p->id\"";
            if($p->id == $value) {
                $options .= ' selected="selected"';
            }
            $options .= '>' . htmlspecialchars($p->$show, ENT_COMPAT, APP_CHARSET) . '</option>';
        }
        
        return "<select id=\"$field\" name=\"$name\" $attrs>$options</select>";
    }
    
    /**
     * Campo File
     *
     * @param string $field nombre de campo
     * @param string|array $attrs atributos de campo
     * @return string
     */
    public static function file($field, $attrs = NULL)
    {
        if(is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }
        
        // obtiene el nombre de campo
        $name = self::getFieldName($field);
        // aviso al programador
        if(self::$_multipart){
             Flash::error('Para poder subir ficheros, debe abrir el form con Form::openMultipar()');
        }
        return "<input id=\"$field\" name=\"$name\" type=\"file\" $attrs/>";
    }

    /**
     * Campo textarea
     *
     * @param string $field nombre de campo
     * @param string|array $attrs atributos de campo
     * @param string $value
     * @return string
     */
    public static function textarea($field, $attrs = NULL, $value = NULL)
    {
        if(is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }
        
        // si no se especificó el valor explicitamente
        if($value === NULL) {
            // obtiene name y value para el campo y los carga en el scope
            extract(self::getFieldData($field), EXTR_OVERWRITE);
        } else {
            $name = self::getFieldName($field);
        }
        
        return "<textarea id=\"$field\" name=\"$name\" $attrs>$value</textarea>";
    }
}
