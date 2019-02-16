<?php
/**
 * KumbiaPHP web & app Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @category   KumbiaPHP
 * @package    Helpers
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */
/**
 * Helper para Formularios.
 *
 * @category   KumbiaPHP
 */
class Form
{
    /**
     * Utilizado para generar los id de los radio button,
     * lleva un conteo interno.
     *
     * @var array
     */
    protected static $radios = array();

    /**
     * Utilizado para avisar al programador,si usa Form::file()
     * y no tiene el form mulipart muestra un error.
     *
     * @var bool
     */
    protected static $multipart = false;

    /**
     * Obtiene el valor de un componente tomado
     * del mismo valor del nombre del campo y formulario
     * que corresponda a un atributo del mismo nombre
     * que sea un string, objeto o array.
     *
     * @param string $field
     * @param mixed  $value    valor de campo
     * @param bool   $filter   filtrar caracteres especiales html
     * @param bool   $check    si esta marcado el checkbox
     * @param bool   $is_check
     *
     * @return array devuelve un array de longitud 3 con la forma array(id, name, value)
     */
    public static function getField($field, $value = null, $is_check = false, $filter = true, $check = false)
    {
        // Obtiene considerando el patrón de formato form.field
        $formField = explode('.', $field, 2);
        list($id, $name) = self::fieldName($formField);
        // Verifica en $_POST
        if (Input::hasPost($field)) {
            $value = $is_check ?
            Input::post($field) == $value : Input::post($field);
        } elseif ($is_check) {
            $value = (bool) $check;
        } elseif ($tmp_val = self::getFromModel($formField)) {
            // Autocarga de datos
            $value = $is_check ? $tmp_val == $value : $tmp_val;
        }
        // Filtrar caracteres especiales
        if (!$is_check && $value !== null && $filter) {
            $value = htmlspecialchars($value, ENT_COMPAT, APP_CHARSET);
        }
        // Devuelve los datos
        return array($id, $name, $value);
    }

    /**
     * Devuelve el valor del modelo.
     *
     * @param array $formField array [modelo, campo]
     *
     * @return mixed
     */
    protected static function getFromModel(array $formField)
    {
        $form = View::getVar($formField[0]);
        if (is_scalar($form) || is_null($form)) {
            return $form;
        }
        $form = (object) $form;

        return isset($form->{$formField[1]}) ? $form->{$formField[1]} : null;
    }

    /**
     * Devuelve el nombre y el id de un campo.
     *
     * @param array $field array del explode
     *
     * @return array array(id, name)
     */
    protected static function fieldName(array $field)
    {
        return isset($field[1]) ?
                    array("{$field[0]}_{$field[1]}", "{$field[0]}[{$field[1]}]") : array($field[0], $field[0]);
    }

    /**
     * Obtiene el valor de un componente tomado
     * del mismo valor del nombre del campo y formulario
     * que corresponda a un atributo del mismo nombre
     * que sea un string, objeto o array.
     *
     * @param string $field
     * @param mixed  $value  valor de campo
     * @param bool   $filter filtrar caracteres especiales html
     *
     * @return array devuelve un array de longitud 3 con la forma array(id, name, value)
     */
    public static function getFieldData($field, $value = null, $filter = true)
    {
        return self::getField($field, $value, false, $filter);
    }

    /**
     * Obtiene el valor de un componente check tomado
     * del mismo valor del nombre del campo y formulario
     * que corresponda a un atributo del mismo nombre
     * que sea un string, objeto o array.
     *
     * @param string $field
     * @param string $checkValue
     * @param bool   $checked
     *
     * @return array Devuelve un array de longitud 3 con la forma array(id, name, checked);
     */
    public static function getFieldDataCheck($field, $checkValue, $checked = false)
    {
        return self::getField($field, $checkValue, true, false, $checked);
    }

    /**
     * @param string       $tag
     * @param string       $field
     * @param string       $value
     * @param string|array $attrs
     */
    protected static function tag($tag, $field, $attrs = '', $value = null, $extra = '', $close = true)
    {
        $attrs = Tag::getAttrs($attrs);
        $end = $close ? ">{{value}}</$tag>" : '/>';
        // Obtiene name, id y value (solo para autoload) para el campo y los carga en el scope
        list($id, $name, $value) = self::getFieldData($field, $value);

        return str_replace('{{value}}', $value, "<$tag id=\"$id\" name=\"$name\" $extra $attrs $end");
    }

    /*
     * Crea un campo input
     *
     * @param string|array $attrs Atributos de campo (opcional)
     * @param string $type
     * @param string $field
     * @param string $value
     * @return string
     */
    public static function input($type, $field, $attrs = '', $value = null)
    {
        return self::tag('input', $field, $attrs, $value, "type=\"$type\" value=\"{{value}}\"", false);
    }

    /**
     * Crea una etiqueta de formulario.
     *
     * @param string $action Acción del formulario (opcional)
     * @param string $method Por defecto es post (opcional)
     * @param string $attrs  Atributos de etiqueta (opcional)
     *
     * @return string
     */
    public static function open($action = '', $method = 'post', $attrs = '')
    {
        $attrs = Tag::getAttrs($attrs);
        if ($action) {
            $action = PUBLIC_PATH.$action;
        } else {
            $action = PUBLIC_PATH.ltrim(Router::get('route'), '/');
        }

        return "<form action=\"$action\" method=\"$method\" $attrs>";
    }

    /**
     * Crea una etiqueta de formulario multipart.
     *
     * @param string       $action Acción del formulario (opcional)
     * @param string|array $attrs  Atributos de etiqueta (opcional)
     *
     * @return string
     */
    public static function openMultipart($action = null, $attrs = '')
    {
        self::$multipart = true;
        if (is_array($attrs)) {
            $attrs['enctype'] = 'multipart/form-data';
            $attrs = Tag::getAttrs($attrs);
        } else {
            $attrs .= ' enctype="multipart/form-data"';
        }

        return self::open($action, 'post', $attrs);
    }

    /**
     * Crea una etiqueta para cerrar un formulario.
     *
     * @return string
     */
    public static function close()
    {
        self::$multipart = false;

        return '</form>';
    }

    /**
     * Crea un botón de submit para el formulario actual.
     *
     * @param string       $text  Texto del botón
     * @param string|array $attrs Atributos de campo (opcional)
     *
     * @return string
     */
    public static function submit($text, $attrs = '')
    {
        return self::button($text, $attrs, 'submit');
    }

    /**
     * Crea un botón reset.
     *
     * @param string       $text  Texto del botón
     * @param string|array $attrs Atributos de campo (opcional)
     *
     * @return string
     */
    public static function reset($text, $attrs = '')
    {
        return self::button($text, $attrs, 'reset');
    }

    /**
     * Crea un botón.
     *
     * @param string       $text  Texto del botón
     * @param array|string $attrs Atributos de campo (opcional)
     * @param string       $type  tipo de botón
     * @param string       $value Valor para el boton
     *
     * @todo FALTA AGREGAR NOMBRE YA QUE SIN ESTE EL VALUE NO LLEGA AL SERVER
     *
     * @return string
     */
    public static function button($text, $attrs = '', $type = 'button', $value = null)
    {
        $attrs = Tag::getAttrs($attrs);
        $value = is_null($value) ? '' : "value=\"$value\"";

        return "<button type=\"$type\" $value $attrs>$text</button>";
    }

    /**
     * Crea un label.
     *
     * @param string $text  Texto a mostrar
     * @param string $field Campo al que hace referencia
     * @param string|array Atributos de campo (opcional)
     *
     * @return string
     */
    public static function label($text, $field, $attrs = '')
    {
        $attrs = Tag::getAttrs($attrs);

        return "<label for=\"$field\" $attrs>$text</label>";
    }

    /**
     * Crea un campo text.
     *
     * @param string       $field Nombre de campo
     * @param string|array $attrs Atributos de campo (opcional)
     * @param string       $value (opcional)
     *
     * @return string
     */
    public static function text($field, $attrs = '', $value = null)
    {
        return self::input('text', $field, $attrs, $value);
    }

    /**
     * Crea un campo select.
     *
     * @param string       $field  Nombre de campo
     * @param array        $data   Array de valores para la lista desplegable
     * @param string|array $attrs  Atributos de campo (opcional)
     * @param string|array $value  Array para select multiple (opcional)
     * @param string       $blank  agrega un item vacio si es diferente de empty
     * @param string       $itemId En caso de usar array de objeto propiedad a tomar como id
     * @param string       $show   texto a mostrar, si es empty usa el to string
     *
     * @return string
     */
    public static function select($field, $data, $attrs = '', $value = null, $blank = '', $itemId = 'id', $show = '')
    {
        $attrs = Tag::getAttrs($attrs);
        // Obtiene name, id y value (solo para autoload) para el campo y los carga en el scope
        list($id, $name, $value) = self::getFieldData($field, $value);
        //Si se quiere agregar blank
        $options = empty($blank) ? '' :
        '<option value="">'.htmlspecialchars($blank, ENT_COMPAT, APP_CHARSET).'</option>';
        foreach ($data as $k => $v) {
            $val = self::selectValue($v, $k, $itemId);
            $text = self::selectShow($v, $show);
            $selected = self::selectedValue($value, $val);
            $options .= "<option value=\"$val\" $selected>$text</option>";
        }

        return "<select id=\"$id\" name=\"$name\" $attrs>$options</select>";
    }

    /**
     * Retorna el value de un item de un select.
     *
     * @param mixed  $item item de un array
     * @param string $key  valor de item dentro del select
     * @param string $id   valor posible de la propiedad del objecto para el value
     *
     * @return string
     */
    public static function selectValue($item, $key, $id)
    {
        return htmlspecialchars(is_object($item) ? $item->$id : $key,
                                ENT_COMPAT, APP_CHARSET);
    }

    /**
     * retorna el atributo para que quede seleccionado el item de un
     * select.
     *
     * @param string|array $value valor(es) que deben estar seleccionados
     * @param string       $key   valor del item actual
     *
     * @return string
     */
    public static function selectedValue($value, $key)
    {
        return ((is_array($value) && in_array($key, $value)) || ($key == $value)) ?
                'selected="selected"' : '';
    }

    /**
     * Retorna el valor a mostrar del item del select.
     *
     * @param mixed  $item item del array
     * @param string $show propiedad el objeto
     *
     * @return string
     */
    public static function selectShow($item, $show)
    {
        $value = (is_object($item) && !empty($show)) ? $item->$show : (string) $item;

        return htmlspecialchars($value, ENT_COMPAT, APP_CHARSET);
    }

    /**
     * Crea un campo checkbox.
     *
     * @param string       $field      Nombre de campo
     * @param string       $checkValue Valor en el checkbox
     * @param string|array $attrs      Atributos de campo (opcional)
     * @param bool         $checked    Indica si se marca el campo (opcional)
     *
     * @return string
     */
    public static function check($field, $checkValue, $attrs = '', $checked = false)
    {
        $attrs = Tag::getAttrs($attrs);
        // Obtiene name y id para el campo y los carga en el scope
        list($id, $name, $checked) = self::getFieldDataCheck($field, $checkValue, $checked);

        if ($checked) {
            $checked = 'checked="checked"';
        }

        return "<input id=\"$id\" name=\"$name\" type=\"checkbox\" value=\"$checkValue\" $attrs $checked/>";
    }

    /**
     * Crea un campo radio button.
     *
     * @param string       $field      Nombre de campo
     * @param string       $radioValue Valor en el radio
     * @param string|array $attrs      Atributos de campo (opcional)
     * @param bool         $checked    Indica si se marca el campo (opcional)
     *
     * @return string
     */
    public static function radio($field, $radioValue, $attrs = '', $checked = false)
    {
        $attrs = Tag::getAttrs($attrs);
        // Obtiene name y id para el campo y los carga en el scope
        list($id, $name, $checked) = self::getFieldDataCheck($field, $radioValue, $checked);

        if ($checked) {
            $checked = 'checked="checked"';
        }

        // contador de campos radio
        if (isset(self::$radios[$field])) {
            ++self::$radios[$field];
        } else {
            self::$radios[$field] = 0;
        }
        $id .= self::$radios[$field];

        return "<input id=\"$id\" name=\"$name\" type=\"radio\" value=\"$radioValue\" $attrs $checked/>";
    }

    /**
     * Crea un botón de tipo imagen.
     *
     * @param string       $img   Nombre o ruta de la imagen
     * @param string|array $attrs Atributos de campo (opcional)
     *
     * @return string
     */
    public static function submitImage($img, $attrs = '')
    {
        $attrs = Tag::getAttrs($attrs);

        return '<input type="image" src="'.PUBLIC_PATH."img/$img\" $attrs/>";
    }

    /**
     * Crea un campo hidden.
     *
     * @param string       $field Nombre de campo
     * @param string|array $attrs Atributos de campo (opcional)
     * @param string       $value
     *
     * @return string
     */
    public static function hidden($field, $attrs = '', $value = null)
    {
        return self::input('hidden', $field, $attrs, $value);
    }

    /**
     * Crea un campo password.
     *
     * @deprecated Obsoleta desde la versión 1.0, usar password
     *
     * @param string       $field Nombre de campo
     * @param string|array $attrs Atributos de campo (opcional)
     * @param string       $value
     */
    public static function pass($field, $attrs = '', $value = null)
    {
        return self::password($field, $attrs, $value);
    }

    /**
     * Crea un campo passwordop.
     *
     * @param string       $field Nombre de campo
     * @param string|array $attrs Atributos de campo (opcional)
     * @param string       $value
     */
    public static function password($field, $attrs = '', $value = null)
    {
        return self::input('password', $field, $attrs, $value);
    }

    /**
     * Crea un campo select que toma los valores de un array de objetos.
     *
     * @param string       $field Nombre de campo
     * @param string       $show  Campo que se mostrara (opcional)
     * @param array        $data  Array('modelo','metodo','param') (opcional)
     * @param string       $blank Campo en blanco (opcional)
     * @param string|array $attrs Atributos de campo (opcional)
     * @param string|array $value (opcional) Array en select multiple
     *
     * @return string
     */
    public static function dbSelect($field, $show = null, $data = null, $blank = 'Seleccione', $attrs = '', $value = null)
    {
        $model = ($data === null) ? substr($field, strpos($field, '.') + 1, -3) : $data[0];
        $model = Util::camelcase($model);
        $model_asoc = new $model();
        //por defecto el primer campo no pk
        $show = $show ?: $model_asoc->non_primary[0];
        $pk = $model_asoc->primary_key[0];
        if ($data === null) {
            $data = $model_asoc->find("columns: $pk,$show", "order: $show asc"); //mejor usar array
        } else {
            $data = (isset($data[2])) ?
            $model_asoc->{$data[1]}($data[2]) :
            $model_asoc->{$data[1]}();
        }

        return self::select($field, $data, $attrs, $value, $blank, $pk, $show);
    }

    /**
     * Crea un campo file.
     *
     * @param string       $field Nombre de campo
     * @param string|array $attrs Atributos de campo (opcional)
     *
     * @return string
     */
    public static function file($field, $attrs = '')
    {
        // aviso al programador
        if (!self::$multipart) {
            Flash::error('Para poder subir ficheros, debe abrir el form con Form::openMultipart()');
        }

        $attrs = Tag::getAttrs($attrs);

        // Obtiene name y id, y los carga en el scope
        list($id, $name) = self::getFieldData($field, false);

        return "<input id=\"$id\" name=\"$name\" type=\"file\" $attrs/>";
    }

    /**
     * Crea un campo textarea.
     *
     * @param string       $field Nombre de campo
     * @param string|array $attrs Atributos de campo (opcional)
     * @param string       $value (opcional)
     *
     * @return string
     */
    public static function textarea($field, $attrs = '', $value = null)
    {
        return self::tag('textarea', $field, $attrs, $value);
    }

    /**
     * Crea un campo fecha nativo (HTML5).
     *
     * @param string       $field Nombre de campo
     * @param string|array $attrs Atributos de campo (opcional)
     * @param string       $value (opcional)
     *
     * @return string
     */
    public static function date($field, $attrs = '', $value = null)
    {
        return self::input('date', $field, $attrs, $value);
    }

    /**
     * Crea un campo de texo para fecha (Requiere JS ).
     *
     * @param string       $field Nombre de campo
     * @param string       $class Clase de estilo (opcional)
     * @param string|array $attrs Atributos de campo (opcional)
     * @param string       $value (opcional)
     *
     * @return string
     */
    public static function datepicker($field, $class = '', $attrs = '', $value = null)
    {
        return self::tag('input', $field, $attrs, null, "class=\"js-datepicker $class\" type=\"text\" value=\"$value\" ");
    }

    /**
     * Crea un campo tiempo nativo (HTML5).
     *
     * @param string       $field Nombre de campo
     * @param string|array $attrs Atributos de campo (opcional)
     * @param string       $value (opcional)
     *
     * @return string
     */
    public static function time($field, $attrs = '', $value = null)
    {
        return self::input('time', $field, $attrs, $value);
    }

    /**
     * Crea un campo fecha/tiempo nativo (HTML5).
     *
     * @param string       $field Nombre de campo
     * @param string|array $attrs Atributos de campo (opcional)
     * @param string       $value (opcional)
     *
     * @return string
     */
    public static function datetime($field, $attrs = '', $value = null)
    {
        return self::input('datetime', $field, $attrs, $value);
    }

    /**
     * Crea un campo numerico nativo (HTML5).
     *
     * @param string       $field Nombre de campo
     * @param string|array $attrs Atributos de campo (opcional)
     * @param string       $value (opcional)
     *
     * @return string
     */
    public static function number($field, $attrs = '', $value = null)
    {
        return self::input('number', $field, $attrs, $value);
    }

    /**
     * Crea un campo url nativo (HTML5).
     *
     * @param string       $field Nombre de campo
     * @param string|array $attrs Atributos de campo (opcional)
     * @param string       $value (opcional)
     *
     * @return string
     */
    public static function url($field, $attrs = '', $value = null)
    {
        return self::input('url', $field, $attrs, $value);
    }

    /**
     * Crea un campo email nativo (HTML5).
     *
     * @param string       $field Nombre de campo
     * @param string|array $attrs Atributos de campo (opcional)
     * @param string       $value (opcional)
     *
     * @return string
     */
    public static function email($field, $attrs = '', $value = null)
    {
        return self::input('email', $field, $attrs, $value);
    }
}
