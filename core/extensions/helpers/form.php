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
     * @return mixed
     */
    public static function getFieldData($field, $value = null)
    {
        // Obtiene considerando el patrón de formato form.field
        $formField = explode('.', $field, 2);

        // Formato modelo.campo
        if (isset($formField[1])) {
            // Id de campo
            $id = "{$formField[0]}_{$formField[1]}";
            // Nombre de campo
            $name = "{$formField[0]}[{$formField[1]}]";

            // Verifica en $_POST
            if (isset($_POST[$formField[0]][$formField[1]])) {
                $value = $_POST[$formField[0]][$formField[1]];
            } elseif ($value === null) {
                // Autocarga de datos
                $form = View::getVar($formField[0]);
                if (is_array($form)) {
                    if (isset($form[$formField[1]]))
                        $value = $form[$formField[1]];
                } elseif (is_object($form)) {
                    if (isset($form->$formField[1]))
                        $value = $form->{$formField[1]};
                }
            }
        } else {
            // Asignacion de Id y Nombre de campo
            $id = $name = $field;

            // Verifica en $_POST
            if (isset($_POST[$field])) {
                $value = $_POST[$field];
            } elseif ($value === null) {
                // Autocarga de datos
                $value = View::getVar($field);
            }
        }

        // Filtrar caracteres especiales
        if ($value !== null) {
            $value = htmlspecialchars($value, ENT_COMPAT, APP_CHARSET);
        }

        // Devuelve los datos
        return array('id' => $id, 'name' => $name, 'value' => $value);
    }

    /**
     * Obtiene el valor del campo por autocarga de valores
     * 
     * @param string $field Nombre de campo
     * @return mixed retorna NULL si no existe valor por autocarga
     */
    public static function getFieldValue($field)
    {
        // Obtiene considerando el patrón de formato form.field
        $formField = explode('.', $field, 2);

        // Formato modelo.campo
        if (isset($formField[1])) {
            // Verifica en $_POST
            if (isset($_POST[$formField[0]][$formField[1]])) {
                $value = $_POST[$formField[0]][$formField[1]];
            } elseif ($value === null) {
                // Autocarga de datos
                $form = View::getVar($formField[0]);
                if (is_array($form)) {
                    if (isset($form[$formField[1]]))
                        $value = $form[$formField[1]];
                } elseif (is_object($form)) {
                    if (isset($form->$formField[1]))
                        $value = $form->{$formField[1]};
                }
            }
        } else {
            // Verifica en $_POST
            if (isset($_POST[$field])) {
                $value = $_POST[$field];
            } elseif ($value === null) {
                // Autocarga de datos
                $value = View::getVar($field);
            }
        }

        // Filtrar caracteres especiales
        if ($value !== null) {
            return htmlspecialchars($value, ENT_COMPAT, APP_CHARSET);
        }

        // Devuelve null
        return null;
    }

    /**
     * Crea un campo input "genérico"
     * 
     * @param string $type
     * @param string $field
     * @param string|array $attrs Atributos de campo (opcional)
     * @param string $value Valor de campo (opcional)
     * @return string 
     */
    public static function in($type, $field, $attrs = NULL, $value = NULL)
    {
        if (is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }

        // Obtiene name, id y value (solo para autoload) para el campo y los carga en el scope
        extract(self::getFieldData($field, $value), EXTR_OVERWRITE);

        return "<input id=\"$id\" name=\"$name\" type=\"$type\" value=\"$value\" $attrs/>";
    }

    /**
     * Crea un campo input
     *
     * @param string|array $attrs Atributos de campo (opcional)
     * @param string $content Contenido interno (opcional)
     * @return string
     */
    public static function input($attrs = NULL, $content = NULL)
    {
        if (is_array($attrs)) {
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
            $action = PUBLIC_PATH . substr(Router::get('route'), 1);
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
     * @param string $value Valor de campo (opcional)
     * @return string
     */
    public static function text($field, $attrs = NULL, $value = NULL)
    {
        return self::in('text', $field, $attrs, $value);
    }

    /**
     * Crea un campo select
     *
     * @param string $field Nombre de campo
     * @param string $data Array de valores para la lista desplegable
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
        extract(self::getFieldData($field, $value), EXTR_OVERWRITE);

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
     * @param string $checked Indica si se marca el campo (opcional)
     * @return string
     */
    public static function check($field, $checkValue, $attrs = NULL, $checked = NULL)
    {
        if (is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }

        // Obtiene name y id para el campo y los carga en el scope
        extract(self::getFieldData($field, $checked), EXTR_OVERWRITE);

        if ($checked || ($checked === NULL && $checkValue == $value)) {
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
     * @param string $checked Indica si se marca el campo (opcional)
     * @return string
     */
    public static function radio($field, $radioValue, $attrs = NULL, $checked = NULL)
    {
        if (is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }

        // Obtiene name y id para el campo y los carga en el scope
        extract(self::getFieldData($field, $checked), EXTR_OVERWRITE);

        if ($checked || ($checked === NULL && $radioValue == $value)) {
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
     * @param string $value Valor de campo (opcional)
     * @return string
     */
    public static function hidden($field, $attrs = NULL, $value = NULL)
    {
        return self::in('hidden', $field, $attrs, $value);
    }

    /**
     * Crea un campo password
     *
     * @param string $field Nombre de campo
     * @param string|array $attrs Atributos de campo (opcional)
     * @param string $value Valor de campo (opcional)
     */
    public static function pass($field, $attrs = NULL, $value = NULL)
    {
        return self::in('password', $field, $attrs, $value);
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
        extract(self::getFieldData($field, $value), EXTR_OVERWRITE);

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
        extract(self::getFieldData($field, false), EXTR_OVERWRITE);

        return "<input id=\"$id\" name=\"$name\" type=\"file\" $attrs/>";
    }

    /**
     * Crea un campo textarea
     *
     * @param string $field Nombre de campo
     * @param string|array $attrs Atributos de campo (opcional)
     * @param string $value Valor de campo (opcional)
     * @return string
     */
    public static function textarea($field, $attrs = NULL, $value = NULL)
    {
        if (is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }

        // Obtiene name, id y value (solo para autoload) para el campo y los carga en el scope
        extract(self::getFieldData($field, $value), EXTR_OVERWRITE);

        return "<textarea id=\"$id\" name=\"$name\" $attrs>$value</textarea>";
    }

    /**
     * Crea un campo fecha
     *
     * @param string $field Nombre de campo
     * @param string $class Clase de estilo (opcional)
     * @param string|array $attrs Atributos de campo (opcional)
     * @param string $value Valor de campo (opcional)
     * @return string
     */
    public static function date($field, $class = NULL, $attrs = NULL, $value = NULL)
    {
        if (is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }

        // Obtiene name, id y value (solo para autoload) para el campo y los carga en el scope
        extract(self::getFieldData($field, $value), EXTR_OVERWRITE);

        return "<input id=\"$id\" name=\"$name\" class=\"js-datepicker $class\" type=\"date\" value=\"$value\" $attrs/>";
    }

    /**
     * Crea un campo search
     *
     * @param string $field Nombre de campo
     * @param string|array $attrs Atributos de campo (opcional)
     * @param string $value Valor de campo (opcional)
     * @return string
     */
    public static function search($field, $attrs = NULL, $value = NULL)
    {
        return self::in('search', $field, $attrs, $value);
    }

    /**
     * Crea un campo tel
     *
     * @param string $field Nombre de campo 
     * @param string|array $attrs Atributos de campo (opcional)
     * @param string $value Valor de campo (opcional)
     * @return string
     */
    public static function tel($field, $attrs = NULL, $value = NULL)
    {
        return self::in('tel', $field, $attrs, $value);
    }

    /**
     * Crea un campo url
     *
     * @param string $field Nombre de campo 
     * @param string|array $attrs Atributos de campo (opcional)
     * @param string $value Valor de campo (opcional)
     * @return string
     */
    public static function url($field, $attrs = NULL, $value = NULL)
    {
        return self::in('url', $field, $attrs, $value);
    }

    /**
     * Crea un campo email
     *
     * @param string $field Nombre de campo 
     * @param string|array $attrs Atributos de campo (opcional)
     * @param string $value Valor de campo (opcional)
     * @return string
     */
    public static function email($field, $attrs = NULL, $value = NULL)
    {
        return self::in('email', $field, $attrs, $value);
    }

    /**
     * Crea un campo datetime
     *
     * @param string $field Nombre de campo
     * @param string|array $attrs Atributos de campo (opcional)
     * @param string $value Valor de campo (opcional)
     * @return string
     */
    public static function datetime($field, $attrs = NULL, $value = NULL)
    {
        return self::in('datetime', $field, $attrs, $value);
    }

    /**
     * Crea un campo date
     *
     * @param string $field Nombre de campo
     * @param string|array $attrs Atributos de campo (opcional)
     * @param string $value Valor de campo (opcional)
     * @return string
     */
    public static function dateNew($field, $attrs = NULL, $value = NULL)
    {
        return self::in('date', $field, $attrs, $value);
    }

    /**
     * Crea un campo month
     *
     * @param string $field Nombre de campo
     * @param string|array $attrs Atributos de campo (opcional)
     * @param string $value Valor de campo (opcional)
     * @return string
     */
    public static function month($field, $attrs = NULL, $value = NULL)
    {
        return self::in('month', $field, $attrs, $value);
    }

    /**
     * Crea un campo week
     *
     * @param string $field Nombre de campo
     * @param string|array $attrs Atributos de campo (opcional)
     * @param string $value Valor de campo (opcional)
     * @return string
     */
    public static function week($field, $attrs = NULL, $value = NULL)
    {
        return self::in('week', $field, $attrs, $value);
    }

    /**
     * Crea un campo time
     *
     * @param string $field Nombre de campo
     * @param string|array $attrs Atributos de campo (opcional)
     * @param string $value Valor de campo (opcional)
     * @return string
     */
    public static function time($field, $attrs = NULL, $value = NULL)
    {
        return self::in('time', $field, $attrs, $value);
    }

    /**
     * Crea un campo datetime-local
     *
     * @param string $field Nombre de campo
     * @param string|array $attrs Atributos de campo (opcional)
     * @param string $value Valor de campo (opcional)
     * @return string
     */
    public static function datetimeLocal($field, $attrs = NULL, $value = NULL)
    {
        return self::in('datetime-local', $field, $attrs, $value);
    }

    /**
     * Crea un campo number
     *
     * @param string $field Nombre de campo
     * @param string|array $attrs Atributos de campo (opcional)
     * @param string $value Valor de campo (opcional)
     * @return string
     */
    public static function number($field, $attrs = NULL, $value = NULL)
    {
        return self::in('number', $field, $attrs, $value);
    }

    /**
     * Crea un campo range
     *
     * @param string $field Nombre de campo
     * @param string|array $attrs Atributos de campo (opcional)
     * @param string $value Valor de campo (opcional)
     * @return string
     */
    public static function range($field, $max=100, $min=0, $step=1, $attrs = NULL, $value = NULL)
    {
        return self::in('range', $field, "max=\"$max\" min=\"$min\" step=\"$step\" $attrs", $value);
    }

    /**
     * Crea un campo color
     *
     * @param string $field Nombre de campo
     * @param string|array $attrs Atributos de campo (opcional)
     * @param string $value Valor de campo (opcional)
     * @return string
     */
    public static function color($field, $attrs = NULL, $value = NULL)
    {
        return self::in('color', $field, $attrs, $value);
    }

}
