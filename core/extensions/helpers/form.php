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
 * @copyright  Copyright (c) 2005-2014 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

/**
 * Helper para Formularios
 *
 * @category   KumbiaPHP
 * @package    Helpers
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
     * @param mixed $value valor de campo
     * @param boolean $filter filtrar caracteres especiales html
     * @return Array devuelve un array de longitud 3 con la forma array(id, name, value)
     */
    public static function getFieldData($field, $value = null, $filter = true)
    {
        // Obtiene considerando el patrón de formato form.field
        $formField = explode('.', $field, 2);
        
        // Formato modelo.campo
        if(isset($formField[1])) {
			// Id de campo
            $id = "{$formField[0]}_{$formField[1]}";
            // Nombre de campo
            $name = "{$formField[0]}[{$formField[1]}]";
			
			// Verifica en $_POST
			if(isset($_POST[$formField[0]][$formField[1]])) {
				$value = $_POST[$formField[0]][$formField[1]];
			} elseif($value === null) { 
				// Autocarga de datos
				$form = View::getVar($formField[0]);
				if(is_array($form) && isset($form[$formField[1]])) {
					$value = $form[$formField[1]];
				} elseif(is_object($form) && isset($form->$formField[1])) {
					$value = $form->{$formField[1]};
				}
			}
		} else {
			// Asignacion de Id y Nombre de campo
			$id = $name = $field;
			
			// Verifica en $_POST
			if(isset($_POST[$field])) {
				$value = $_POST[$field];
			} elseif($value === null) { 
				// Autocarga de datos
				$value = View::getVar($field);
			}
		}

        // Filtrar caracteres especiales
        if ($value !== null && $filter) {
            $value = htmlspecialchars($value, ENT_COMPAT, APP_CHARSET);
        }

		// Devuelve los datos
        return array($id, $name, $value);
    }
    
	/**
     * Obtiene el valor de un componente check tomado
     * del mismo valor del nombre del campo y formulario
     * que corresponda a un atributo del mismo nombre
     * que sea un string, objeto o array.
     *
     * @param string $field
     * @param string $checkValue
     * @param boolean $checked
     * @return array Devuelve un array de longitud 3 con la forma array(id, name, checked);
     */
    public static function getFieldDataCheck($field, $checkValue, $checked = null)
    {
        // Obtiene considerando el patrón de formato form.field
        $formField = explode('.', $field, 2);
        
        // Formato modelo.campo
        if(isset($formField[1])) {
			// Id de campo
            $id = "{$formField[0]}_{$formField[1]}";
            // Nombre de campo
            $name = "{$formField[0]}[{$formField[1]}]";
			
			// Verifica en $_POST
			if(isset($_POST[$formField[0]][$formField[1]])) {
				$checked = $_POST[$formField[0]][$formField[1]] == $checkValue;
			} elseif($checked === null) { 
				// Autocarga de datos
				$form = View::getVar($formField[0]);
				if(is_array($form)) {
					$checked = isset($form[$formField[1]]) && $form[$formField[1]] == $checkValue;
				} elseif(is_object($form)) {
					$checked = isset($form->$formField[1]) && $form->$formField[1] == $checkValue;
				}
			}
		} else {
			// Asignacion de Id y Nombre de campo
			$id = $name = $field;
			
			// Verifica en $_POST
			if(isset($_POST[$field])) {
				$checked = $_POST[$field] == $checkValue;
			} elseif($checked === null) { 
				// Autocarga de datos
				$checked = View::getVar($field) == $checkValue;
			}
		}

		// Devuelve los datos
        return array($id, $name, $checked);
    }

    /**
     * Obtiene el valor del campo por autocarga de valores
     * 
     * @param string $field nombre de campo
     * @param boolean $filter filtrar caracteres especiales html
     * @return mixed retorna NULL si no existe valor por autocarga
     */
    public static function getFieldValue($field, $filter = true)
    {
		// Obtiene considerando el patrón de formato form.field
        $formField = explode('.', $field, 2);
        
        $value = null;
        
        // Formato modelo.campo
        if(isset($formField[1])) {
			// Verifica en $_POST
			if(isset($_POST[$formField[0]][$formField[1]])) {
				$value = $_POST[$formField[0]][$formField[1]];
			} else { 
				// Autocarga de datos
				$form = View::getVar($formField[0]);
				if(is_array($form) && isset($form[$formField[1]])) {
					$value = $form[$formField[1]];
				} elseif(is_object($form) && isset($form->$formField[1])) {
					$value = $form->{$formField[1]};
				}
			}
		} else {
			// Verifica en $_POST
			if(isset($_POST[$field])) {
				$value = $_POST[$field];
			} else { 
				// Autocarga de datos
				$value = View::getVar($field);
			}
		}

        // Filtrar caracteres especiales
        if ($value !== null && $filter) {
            return htmlspecialchars($value, ENT_COMPAT, APP_CHARSET);
        }
        
        // Devuelve valor
        return $value;
    }

    /**
     * Crea un campo input
     *
     * @param string|array $attrs Atributos de campo (opcional)
     * @param string $type
     * @param string $field
     * @param string $value
     * @return string
     */
    public static function input($type, $field,$attrs = NULL, $value=NULL)
    {
       
        if (is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }
         // Obtiene name, id y value (solo para autoload) para el campo y los carga en el scope
        list($id, $name, $value) = self::getFieldData($field, $value);
        return "<input id=\"$id\" name=\"$name\" type=\"$type\" value=\"$value\" $attrs/>";
    }

    /**
     * Crea una etiqueta de formulario
     *
     * @param string $action Acción del formulario (opcional)
     * @param string $method Por defecto es post (opcional)
     * @param string|array $attrs Atributos de etiqueta (opcional)
     * @return string
     */
    public static function open($action = NULL, $method = 'post', $attrs = NULL)
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
     * @param string $action Acción del formulario (opcional)
     * @param string|array $attrs Atributos de etiqueta (opcional)
     * @return string
     */
    public static function openMultipart($action = NULL, $attrs = NULL)
    {
        self::$_multipart = TRUE;
        if (is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }
        if ($action) {
            $action = PUBLIC_PATH . $action;
        } else {
            $action = PUBLIC_PATH . ltrim(Router::get('route'), '/');
        }
        return "<form action=\"$action\" method=\"post\" enctype=\"multipart/form-data\" $attrs>";
    }

    /**
     * Crea una etiqueta para cerrar un formulario
     *
     * @return string
     */
    public static function close()
    {
        self::$_multipart = FALSE;
        return '</form>';
    }

    /**
     * Crea un botón de submit para el formulario actual
     *
     * @param string $text Texto del botón
     * @param string|array $attrs Atributos de campo (opcional)
     * @return string
     */
    public static function submit($text, $attrs = NULL)
    {
        if (is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }
        return "<input type=\"submit\" value=\"$text\" $attrs />";
    }

    /**
     * Crea un botón reset
     *
     * @param string $text Texto del botón
     * @param string|array $attrs Atributos de campo (opcional)
     * @return string
     */
    public static function reset($text, $attrs = NULL)
    {
        if (is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }
        return "<input type=\"reset\" value=\"$text\" $attrs />";
    }

    /**
     * Crea un botón
     *
     * @param string $text Texto del botón
     * @param array $attrs Atributos de campo (opcional)
     * @return string
     */
    public static function button($text, $attrs = NULL)
    {
        if (is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }
        return "<input type=\"button\" value=\"$text\" $attrs />";
    }

    /**
     * Crea un label
     *
     * @param string $text Texto a mostrar
     * @param string $field Campo al que hace referencia
     * @param string|array Atributos de campo (opcional)
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
     * Crea un campo text
     *
     * @param string $field Nombre de campo
     * @param string|array $attrs Atributos de campo (opcional)
     * @param string $value (opcional)
     * @return string
     */
    public static function text($field, $attrs = NULL, $value = NULL)
    {
        return self::input('text', $field, $attrs, $value);
    }

    /**
     * Crea un campo select
     *
     * @param string $field Nombre de campo
     * @param array $data Array de valores para la lista desplegable
     * @param string|array $attrs Atributos de campo (opcional)
     * @param string|array $value Array para select multiple (opcional)
     * @return string
     */
    public static function select($field, $data, $attrs = NULL, $value = NULL)
    {
        if (is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }

        // Obtiene name, id y value (solo para autoload) para el campo y los carga en el scope
        list($id, $name, $value) = self::getFieldData($field, $value);

        $options = '';
        foreach ($data as $k => $v) {
            $k = htmlspecialchars($k, ENT_COMPAT, APP_CHARSET);
            $options .= "<option value=\"$k\"";
            // Si es array $value para select multiple se seleccionan todos
            if (is_array($value)) {
                if (in_array($k, $value)) {
                    $options .= ' selected="selected"';
                }
            } else {
                if ($k == $value) {
                    $options .= ' selected="selected"';
                }
            }
            $options .= '>' . htmlspecialchars($v, ENT_COMPAT, APP_CHARSET) . '</option>';
        }

        return "<select id=\"$id\" name=\"$name\" $attrs>$options</select>";
    }

    /**
     * Crea un campo checkbox
     *
     * @param string $field Nombre de campo
     * @param string $checkValue Valor en el checkbox
     * @param string|array $attrs Atributos de campo (opcional)
     * @param boolean $checked Indica si se marca el campo (opcional)
     * @return string
     */
    public static function check($field, $checkValue, $attrs = NULL, $checked = NULL)
    {
        if (is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }
        
        // Obtiene name y id para el campo y los carga en el scope
        list($id, $name, $checked) = self::getFieldDataCheck($field, $checkValue, $checked);

        if ($checked) {
            $checked = 'checked="checked"';
        }

        return "<input id=\"$id\" name=\"$name\" type=\"checkbox\" value=\"$checkValue\" $attrs $checked/>";
    }

    /**
     * Crea un campo radio button
     *
     * @param string $field Nombre de campo
     * @param string $radioValue Valor en el radio
     * @param string|array $attrs Atributos de campo (opcional)
     * @param boolean $checked Indica si se marca el campo (opcional)
     * @return string
     */
    public static function radio($field, $radioValue, $attrs = NULL, $checked = NULL)
    {
        if (is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }

        // Obtiene name y id para el campo y los carga en el scope
        list($id, $name, $checked) = self::getFieldDataCheck($field, $radioValue, $checked);

        if ($checked) {
            $checked = 'checked="checked"';
        }

        // contador de campos radio
        if (isset(self::$_radios[$field])) {
            self::$_radios[$field]++;
        } else {
            self::$_radios[$field] = 0;
        }
        $id .= self::$_radios[$field];

        return "<input id=\"$id\" name=\"$name\" type=\"radio\" value=\"$radioValue\" $attrs $checked/>";
    }

    /**
     * Crea un botón de tipo imagen
     *  
     * @param string $img Nombre o ruta de la imagen
     * @param string|array $attrs Atributos de campo (opcional)
     * @return string
     */
    public static function submitImage($img, $attrs = NULL)
    {
        if (is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }
        return "<input type=\"image\" src=\"" . PUBLIC_PATH . "img/$img\" $attrs/>";
    }

    /**
     * Crea un campo hidden
     *
     * @param string $field Nombre de campo
     * @param string|array $attrs Atributos de campo (opcional)
     * @param string $value
     * @return string
     */
    public static function hidden($field, $attrs = NULL, $value = NULL)
    {
        return self::input('hidden', $field, $attrs, $value);
    }

    /**
     * Crea un campo password
     *
     * @param string $field Nombre de campo
     * @param string|array $attrs Atributos de campo (opcional)
     * @param string $value
     */
    public static function pass($field, $attrs = NULL, $value = NULL)
    {
       return self::input('password',$field, $attrs, $value);
    }

    /**
     * Crea un campo select que toma los valores de un array de objetos
     *
     * @param string $field Nombre de campo
     * @param string $show Campo que se mostrara (opcional)
     * @param array $data Array('modelo','metodo','param') (opcional)
     * @param string $blank Campo en blanco (opcional)
     * @param string|array $attrs Atributos de campo (opcional)
     * @param string|array $value (opcional) Array en select multiple
     * @return string
     */
    public static function dbSelect($field, $show = NULL, $data = NULL, $blank = 'Seleccione', $attrs = NULL, $value = NULL)
    {
        if (is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }

        // Obtiene name, id y value (solo para autoload) para el campo y los carga en el scope
        list($id, $name, $value) = self::getFieldData($field, $value);

        // Si no se envía un campo por defecto, no se crea el tag option
        if ($blank != NULL) {
            $options = '<option value="">' . htmlspecialchars($blank, ENT_COMPAT, APP_CHARSET) . '</option>';
        } else {
            $options = '';
        }

        //por defecto el modelo de modelo(_id)
        if ($data === NULL) {
            $model_asoc = explode('.', $field, 2);
            $model_asoc = substr(end($model_asoc), 0, -3); //se elimina el _id
            $model_asoc = Load::model($model_asoc);
            $pk = $model_asoc->primary_key[0];

            if (!$show) {
                //por defecto el primer campo no pk
                $show = $model_asoc->non_primary[0];
            }

            $data = $model_asoc->find("columns: $pk,$show", "order: $show asc"); //mejor usar array
        } else {
            $model_asoc = Load::model($data[0]);
            $pk = $model_asoc->primary_key[0];

            // Verifica si existe el parámetro
            if (isset($data[2])) {
                $data = $model_asoc->$data[1]($data[2]);
            } else {
                $data = $model_asoc->$data[1]();
            }
        }

        foreach ($data as $p) {
            $options .= "<option value=\"{$p->$pk}\"";
            // Si es array $value para select multiple se seleccionan todos
            if (is_array($value)) {
                if (in_array($p->$pk, $value)) {
                    $options .= ' selected="selected"';
                }
            } else {
                if ($p->$pk == $value) {
                    $options .= ' selected="selected"';
                }
            }
            $options .= '>' . htmlspecialchars($p->$show, ENT_COMPAT, APP_CHARSET) . '</option>';
        }

        return "<select id=\"$id\" name=\"$name\" $attrs>$options</select>" . PHP_EOL;
    }

    /**
     * Crea un campo file
     *
     * @param string $field Nombre de campo
     * @param string|array $attrs Atributos de campo (opcional)
     * @return string
     */
    public static function file($field, $attrs = NULL)
    {
        // aviso al programador
        if (!self::$_multipart) {
            Flash::error('Para poder subir ficheros, debe abrir el form con Form::openMultipart()');
        }
        
        if (is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }
 
        // Obtiene name y id, y los carga en el scope
        list($id, $name, ) = self::getFieldData($field, FALSE);
        return "<input id=\"$id\" name=\"$name\" type=\"file\" $attrs/>";
    }

    /**
     * Crea un campo textarea
     *
     * @param string $field Nombre de campo
     * @param string|array $attrs Atributos de campo (opcional)
     * @param string $value (opcional)
     * @return string
     */
    public static function textarea($field, $attrs = NULL, $value = NULL)
    {
        if (is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }

        // Obtiene name, id y value (solo para autoload) para el campo y los carga en el scope
        list($id, $name, $value) = self::getFieldData($field, $value);

        return "<textarea id=\"$id\" name=\"$name\" $attrs>$value</textarea>";
    }

    /**
     * Crea un campo fecha nativo (HTML5)
     *
     * @param string $field Nombre de campo
     * @param string|array $attrs Atributos de campo (opcional)
     * @param string $value (opcional)
     * @return string
     */
    public static function date($field, $attrs = NULL, $value = NULL)
    {
        return self::input('date',$field, $attrs, $value);
    }
    
     /**
     * Crea un campo de texo para fecha (Requiere JS )
     *
     * @param string $field Nombre de campo
     * @param string $class Clase de estilo (opcional)
     * @param string|array $attrs Atributos de campo (opcional)
     * @param string $value (opcional)
     * @return string
     */
    public static function datepicker($field, $class = NULL, $attrs = NULL, $value = NULL)
    {
        if (is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }

        // Obtiene name, id y value (solo para autoload) para el campo y los carga en el scope
        list($id, $name, $value) = self::getFieldData($field, $value);
	return "<input id=\"$id\" name=\"$name\" class=\"js-datepicker $class\" type=\"text\" value=\"$value\" $attrs/>";

    }

    /**
     * Crea un campo tiempo nativo (HTML5)
     *
     * @param string $field Nombre de campo
     * @param string|array $attrs Atributos de campo (opcional)
     * @param string $value (opcional)
     * @return string
     */
    public static function time($field, $attrs = NULL, $value = NULL)
    {
       return self::input('time',$field, $attrs, $value);
    }

    /**
     * Crea un campo fecha/tiempo nativo (HTML5)
     *
     * @param string $field Nombre de campo
     * @param string|array $attrs Atributos de campo (opcional)
     * @param string $value (opcional)
     * @return string
     */
    public static function datetime($field, $attrs = NULL, $value = NULL)
    {
        return self::input('datetime',$field, $attrs, $value);
    }

    /**
     * Crea un campo numerico nativo (HTML5)
     *
     * @param string $field Nombre de campo
     * @param string|array $attrs Atributos de campo (opcional)
     * @param string $value (opcional)
     * @return string
     */
    public static function number($field, $attrs = NULL, $value = NULL)
    {
        return self::input('number',$field, $attrs, $value);
    }


    /**
     * Crea un campo url nativo (HTML5)
     *
     * @param string $field Nombre de campo
     * @param string|array $attrs Atributos de campo (opcional)
     * @param string $value (opcional)
     * @return string
     */
    public static function url($field, $attrs = NULL, $value = NULL)
    {
        return self::input('url',$field, $attrs, $value);
    }

    /**
     * Crea un campo email nativo (HTML5)
     *
     * @param string $field Nombre de campo
     * @param string|array $attrs Atributos de campo (opcional)
     * @param string $value (opcional)
     * @return string
     */
    public static function email($field, $attrs = NULL, $value = NULL)
    {
        return self::input('email',$field, $attrs, $value);
    }
}
