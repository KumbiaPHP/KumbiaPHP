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
 * @copyright  Copyright (c) 2005 - 2024 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Helper para crear Formularios de un modelo automáticamente.
 *
 * @category   KumbiaPHP
 * @package    Helpers
 */
class ModelForm
{
    /**
     * Generate a form from model automatically
     * -
     * Genera un form de un modelo (objeto) automáticamente.
     *
     */
    public static function create(object $model, string $action = ''): void
    {
        $model_name = $model::class;
        if (!$action) {
            $action = ltrim(Router::get('route'), '/');
        }
        // separar para diferentes ORM u otros formatos json, ini, xml, array,...

        echo '<form action="', PUBLIC_PATH.$action, '" method="post" id="', $model_name, '" class="scaffold">' , PHP_EOL;
        $pk = $model->primary_key[0];
        echo '<input id="', $model_name, '_', $pk, '" name="', $model_name, '[', $pk, ']" class="id" value="', $model->$pk , '" type="hidden">' , PHP_EOL;

        $fields = array_diff($model->fields, [...$model->_at, ...$model->_in, ...$model->primary_key]);

        foreach ($fields as $field) {
            $tipo = trim(preg_replace('/(\(.*\))/', '', $model->_data_type[$field])); //TODO: recoger tamaño y otros valores
            $alias = $model->get_alias($field);
            $formId = $model_name.'_'.$field;
            $formName = $model_name.'['.$field.']';

            if (in_array($field, $model->not_null)) {
                echo "<label class=\"required\">$alias" , PHP_EOL;
                $required = ' required';
            } else {
                echo "<label>$alias" , PHP_EOL;
                $required = '';
            }

            switch ($tipo) {
                case 'tinyint': case 'smallint': case 'mediumint':
                case 'integer': case 'int': case 'bigint':
                case 'float': case 'double': case 'precision':
                case 'real': case 'decimal': case 'numeric':
                case 'year': case 'day': case 'int unsigned': // Números

                    if (str_ends_with($field, '_id')) {
                        echo Form::dbSelect($model_name.'.'.$field, null, null, 'Seleccione', $required, $model->$field);
                        break;
                    }

                    echo "<input id=\"$formId\" type=\"number\" name=\"$formName\" value=\"{$model->$field}\"$required>" , PHP_EOL;
                    break;

                case 'date':
                    echo "<input id=\"$formId\" type=\"date\" name=\"$formName\" value=\"{$model->$field}\"$required>" , PHP_EOL;
                    break;

                case 'datetime': case 'timestamp':
                    echo "<input id=\"$formId\" type=\"datetime-local\" name=\"$formName\" value=\"{$model->$field}\"$required>" , PHP_EOL;
                    break;

                case 'enum': case 'set': case 'bool':
                    $enumList = explode(',', str_replace("'", '', substr($model->_data_type[$field], 5, (strlen($model->_data_type[$field]) - 6))));
                    echo "<select id=\"$formId\" class=\"select\" name=\"$formName\" >", PHP_EOL;
                    foreach ($enumList as $value) {
                        echo "<option value=\"{$value}\">$value</option>", PHP_EOL;
                    }
                    echo '</select>', PHP_EOL;
                    break;

                case 'text': case 'mediumtext': case 'longtext': // Usar textarea
                case 'blob': case 'mediumblob': case 'longblob':
                    echo "<textarea id=\"$formId\" name=\"$formName\"$required>{$model->$field}</textarea>" , PHP_EOL;
                    break;

                default: //text,tinytext,varchar, char,etc se comprobara su tamaño
                    echo "<input id=\"$formId\" type=\"text\" name=\"$formName\" value=\"{$model->$field}\"$required>" , PHP_EOL;
            }
            echo '</label>';
        }
        echo '<input type="submit">' , PHP_EOL;
        echo '</form>' , PHP_EOL; 
    }
}
