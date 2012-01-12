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
 * @copyright  Copyright (c) 2005-2010 KumbiaPHP Team (http://www.kumbiaphp.com)
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
	 * @param boolean $autoload autocarga de valores
     * @return mixed
     */
    protected static function _getFieldData ($field, $autoload = TRUE)
    {
        // Obtiene considerando el patron de formato form.field
        $formField = explode('.', $field, 2);
        
        // Si tiene el formato form.field
        if(isset($formField[1])) {
			// Id de campo
			$id = "{$formField[0]}_{$formField[1]}";
            // Nombre de campo
            $name = "{$formField[0]}[{$formField[1]}]";
			
			// Sin autocarga
			if(!$autoload) {
				return array('id' => $id, 'name' => $name);
			}
			
			// Obtiene el controller
			$controller = Dispatcher::get_controller();
			// Valor por defecto
			$value = NULL;
			
            // Si existe un valor cargado
            if(isset($controller->{$formField[0]})) {
                $form = $controller->{$formField[0]};
                if (is_object($form) && isset($form->{$formField[1]})) {
                    $value = $form->{$formField[1]};
                } elseif (is_array($form) && isset($form[$formField[1]])) {
                    $value = $form[$formField[1]];
                }
            } elseif(isset($_POST[$formField[0]][$formField[1]])) {
				$value = $_POST[$formField[0]][$formField[1]];
			}
        } else { // Formato de campo comun
			// Sin autocarga
			if(!$autoload) {
				return array('id' => $formField[0], 'name' => $formField[0]);
			}
			
            // Nombre de campo y id
            $id = $name = $formField[0];
			// Obtiene el controller
			$controller = Dispatcher::get_controller();
			// Valor por defecto
			$value = NULL;
			
            // Si existe un valor cargado
            if(isset($controller->$name)) {
				$value = $controller->$name;
            } elseif(isset($_POST[$name])) {
				$value = $_POST[$name];
			}
        }
        
        // Filtrar caracteres especiales
        if($value) {
            $value = htmlspecialchars($value, ENT_COMPAT, APP_CHARSET);
        }
        
        return array('id' => $id, 'name' => $name, 'value' => $value);
    }
    
    /**
     * Obtiene el valor del campo por autocarga de valores
     * 
     * @param string $field nombre de campo
     * @return mixed retorna NULL si no existe valor por autocarga
     */
    public static function getFieldValue($field)
    {
		// Obtiene considerando el patron de formato form.field
        $formField = explode('.', $field, 2);
        
        // Obtiene el controller
		$controller = Dispatcher::get_controller();
        
        // Valor por defecto
        $value = NULL;
        
        // Si tiene el formato form.field
        if(isset($formField[1])) {
			
			// Si existe un valor cargado
            if(isset($controller->{$formField[0]})) {
                $form = $controller->{$formField[0]};
                if (is_object($form) && isset($form->{$formField[1]})) {
                    $value = $form->{$formField[1]};
                } elseif (is_array($form) && isset($form[$formField[1]])) {
                    $value = $form[$formField[1]];
                }
            } elseif(isset($_POST[$formField[0]][$formField[1]])) {
				$value = $_POST[$formField[0]][$formField[1]];
			}
			
		} else { // Formato de campo comun
		
			// Si existe un valor cargado
            if(isset($controller->$field)) {
				$value = $controller->$field;
            } elseif(isset($_POST[$field])) {
				$value = $_POST[$field];
			}
			
		}
		
		// Retorna el valor de campo
		return $value;
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
            $action = PUBLIC_PATH . $action;
        } else {
            $action = PUBLIC_PATH . ltrim(Router::get('route'), '/');
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
            $action = PUBLIC_PATH . $action;
        } else {
            $action = PUBLIC_PATH . substr(Router::get('route'), 1);
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
        self::$_multipart = FALSE;
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
        
        // Obtiene name, id y value (solo para autoload) para el campo y los carga en el scope
        extract(self::_getFieldData($field, $value === NULL), EXTR_OVERWRITE);
        
        return "<input id=\"$id\" name=\"$name\" type=\"text\" value=\"$value\" $attrs/>";
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
        
        // Obtiene name, id y value (solo para autoload) para el campo y los carga en el scope
        extract(self::_getFieldData($field, $value === NULL), EXTR_OVERWRITE);
        
        $options = '';
        foreach($data as $k => $v) {
            $k = htmlspecialchars($k, ENT_COMPAT, APP_CHARSET);
            $options .= "<option value=\"$k\"";
            if($k == $value) {
                $options .= ' selected="selected"';
            }
            $options .= '>' . htmlspecialchars($v, ENT_COMPAT, APP_CHARSET) . '</option>';
        }
        
        return "<select id=\"$id\" name=\"$name\" $attrs>$options</select>";
    }
    
    /**
     * Campo checkbox
     *
     * @param string $field nombre de campo
     * @param string $checkValue valor en el checkbox
     * @param string|array $attrs atributos de campo
     * @param string $checked indica si se marca el campo
     * @return string
     */
    public static function check($field, $checkValue, $attrs = NULL, $checked = NULL)
    {
        if(is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }
        
		// Obtiene name y id para el campo y los carga en el scope
		extract(self::_getFieldData($field, $checked === NULL), EXTR_OVERWRITE);
		
        if($checked || ($checked === NULL && $checkValue == $value)) {
            $checked = 'checked="checked"';
        }
        
        return "<input id=\"$id\" name=\"$name\" type=\"checkbox\" value=\"$checkValue\" $attrs $checked/>";
    }
    
    /**
     * Campo radio button
     *
     * @param string $field nombre de campo
     * @param string $radioValue valor en el radio
     * @param string|array $attrs atributos de campo
     * @param string $checked indica si se marca el campo
     * @return string
     */
    public static function radio ($field, $radioValue, $attrs = NULL, $checked = NULL)
    {
        if(is_array($attrs)){
            $attrs = Tag::getAttrs($attrs);
        }
		        
        // Obtiene name y id para el campo y los carga en el scope
		extract(self::_getFieldData($field, $checked === NULL), EXTR_OVERWRITE);
		
        if($checked || ($checked === NULL && $radioValue == $value)) {
            $checked = 'checked="checked"';
        }
        
		// contador de campos radio
		if(isset(self::$_radios[$field])) {
			self::$_radios[$field]++;
		} else {
			self::$_radios[$field] = 0;
		}
		$id .= self::$_radios[$field];
		
        return "<input id=\"$id\" name=\"$name\" type=\"radio\" value=\"$radioValue\" $attrs $checked/>";
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
        
		// Obtiene name, id y value (solo para autoload) para el campo y los carga en el scope
        extract(self::_getFieldData($field, $value === NULL), EXTR_OVERWRITE);
        
        return "<input id=\"$id\" name=\"$name\" type=\"hidden\" value=\"$value\" $attrs/>";
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
        
        // Obtiene name, id y value (solo para autoload) para el campo y los carga en el scope
        extract(self::_getFieldData($field, $value === NULL), EXTR_OVERWRITE);
        
        return "<input id=\"$id\" name=\"$name\" type=\"password\" value=\"$value\" $attrs/>";
    }
    
    /**
     * Campo Select que toma los valores de un array de objetos
     *
     * @param string $field nombre de campo
     * @param string $show campo que se mostrara (opcional)
     * @param array $data array('modelo','metodo','param') (opcional)
     * @param string $blank campo en blanco (opcional)
     * @param string|array $attrs atributos de campo (opcional)
     * @param string $value (opcional)
     * @return string
     */
    public static function dbSelect($field, $show = NULL, $data = NULL, $blank = 'Seleccione', $attrs = NULL, $value = NULL)
    {
        if(is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }
        
        // Obtiene name, id y value (solo para autoload) para el campo y los carga en el scope
        extract(self::_getFieldData($field, $value === NULL), EXTR_OVERWRITE);
        
        $options = '<option value="">' . htmlspecialchars($blank, ENT_COMPAT, APP_CHARSET) . '</option>';

		//por defecto el modelo de modelo(_id)
        if($data === NULL){
			$model_asoc = explode('.', $field, 2);
			$model_asoc = substr(end($model_asoc), 0, -3);//se elimina el _id
			$model_asoc = Load::model($model_asoc);
			$pk = $model_asoc->primary_key[0];	    
			
			if(! $show){
				//por defecto el primer campo no pk
				$show = $model_asoc->non_primary[0];
			}
			
			$data = $model_asoc->find("columns: $pk,$show","order: $show asc");//mejor usar array
		} else {
			$model_asoc = Load::model($data[0]);
			$pk = $model_asoc->primary_key[0];
			
			// Verifica si existe el argumento
			if(isset($data[2])) {
				$data = $model_asoc->$data[1]($data[2]);
			} else {
				$data = $model_asoc->$data[1]();
			}
		}
	
        foreach($data as $p) {
            $options .= "<option value=\"{$p->$pk}\"";
			if($p->$pk == $value) {
                $options .= ' selected="selected"';
            }
            $options .= '>' . htmlspecialchars($p->$show, ENT_COMPAT, APP_CHARSET) . '</option>';
        }
        
        return "<select id=\"$id\" name=\"$name\" $attrs>$options</select>".PHP_EOL;
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
        // aviso al programador
        if(!self::$_multipart){
             Flash::error('Para poder subir ficheros, debe abrir el form con Form::openMultipart()');
        }
		
        if(is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }
        
        // Obtiene name y id, y los carga en el scope
        extract(self::_getFieldData($field, false), EXTR_OVERWRITE);
				
        return "<input id=\"$id\" name=\"$name\" type=\"file\" $attrs/>";
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
        
        // Obtiene name, id y value (solo para autoload) para el campo y los carga en el scope
        extract(self::_getFieldData($field, $value === NULL), EXTR_OVERWRITE);
        
        return "<textarea id=\"$id\" name=\"$name\" $attrs>$value</textarea>";
    }
	
	/**
     * Campo fecha
     *
     * @param string $field nombre de campo
	 * @param string $class clase de estilo
     * @param string|array $attrs atributos de campo
     * @param string $value
     * @return string
     */
    public static function date($field, $class = NULL, $attrs = NULL, $value = NULL)
    {
        if(is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }
        
        // Obtiene name, id y value (solo para autoload) para el campo y los carga en el scope
        extract(self::_getFieldData($field, $value === NULL), EXTR_OVERWRITE);
        
        return "<input id=\"$id\" name=\"$name\" class=\"js-datepicker $class\" type=\"date\" value=\"$value\" $attrs/>";
    }
}
