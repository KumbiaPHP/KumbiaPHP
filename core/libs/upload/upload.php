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
 * @category   Kumbia
 * @package    Upload
 * @copyright  Copyright (c) 2005-2012 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

/**
 * Sube archivos al servidor.
 *
 * @category   Kumbia
 * @package    Upload
 */
abstract class Upload
{

    /**
     * Nombre de archivo subido por método POST
     * 
     * @var string
     */
    protected $_name;
    /**
     * Permitir subir archivos de scripts ejecutables
     *
     * @var boolean
     */
    protected $_allowScripts = FALSE;
    /**
     * Tamaño mínimo del archivo
     * 
     * @var string
     */
    protected $_minSize = NULL;
    /**
     * Tamaño máximo del archivo
     *
     * @var string
     */
    protected $_maxSize = NULL;
    /**
     * Tipos de archivo permitidos utilizando mime
     * 
     * @var array
     */
    protected $_types = NULL;
    /**
     * Extensión de archivo permitida
     *
     * @var array
     */
    protected $_extensions = NULL;
    /**
     * Permitir sobrescribir ficheros
     * 
     * @var bool Por defecto FALSE
     */
    protected $_overwrite = FALSE;

    /**
     * Constructor
     *
     * @param string $name nombre de archivo por método POST
     */
    public function __construct($name)
    {
        $this->_name = $name;
    }

    /**
     * Indica si se permitirá guardar archivos de scripts ejecutables
     *
     * @param boolean $value
     */
    public function setAllowScripts($value)
    {
        $this->_allowScripts = $value;
    }

    /**
     * Asigna el tamaño mínimo permitido para el archivo
     *
     * @param string $size
     */
    public function setMinSize($size)
    {
        $this->_minSize = trim($size);
    }

    /**
     * Asigna el tamaño máximo permitido para el archivo
     *
     * @param string $size
     */
    public function setMaxSize($size)
    {
        $this->_maxSize = trim($size);
    }

    /**
     * Asigna los tipos de archivos permitido (mime)
     *
     * @param array|string $value lista de tipos de archivos permitidos (mime) si es string separado por |
     */
    public function setTypes($value)
    {
        if (!is_array($value))
            $value = explode('|', $value);
        $this->_types = $value;
    }

    /**
     * Asigna las extensiones de archivos permitidas
     *
     * @param array|string $value lista de extensiones para archivos, si es string separado por |
     */
    public function setExtensions($value)
    {
        if (!is_array($value))
            $value = explode('|', $value);
        $this->_extensions = $value;
    }

    /**
     * Permitir sobrescribir el fichero
     *
     * @param bool $value
     */
    public function overwrite($value)
    {
        $this->_overwrite = (bool) $value;
    }

    /**
     * Acciones antes de guardar
     *
     * @param string $name nombre con el que se va a guardar el archivo
     * @return boolean
     */
    protected function _beforeSave($name)
    {
        
    }

    /**
     * Acciones después de guardar
     * 
     * @param string $name nombre con el que se guardo el archivo
     */
    protected function _afterSave($name)
    {

    }

    /**
     * Guarda el archivo subido
     *
     * @param string $name nombre con el que se guardara el archivo
     * @return boolean|string Nombre de archivo generado con la extensión o FALSE si falla
     */
    public function save($name = NULL)
    {
        if (!$this->isUploaded()) {
            return FALSE;
        }
        if (!$name) {
            $name = $_FILES[$this->_name]['name'];
        } else {
            $name = $name . $this->_getExtension();
        }
        // Guarda el archivo
        if ($this->_beforeSave($name) !== FALSE && $this->_overwrite($name) && $this->_validates() && $this->_saveFile($name)) {
            $this->_afterSave($name);
            return $name;
        }
        return FALSE;
    }

    /**
     * Guarda el archivo con un nombre aleatorio
     * 
     * @return string|boolean Nombre de archivo generado o FALSE si falla
     */
    public function saveRandom()
    {
        // Genera el nombre de archivo
        $name = md5(time());

        // Guarda el archivo
        if ($this->save($name)) {
            return $name . $this->_getExtension();
        }

        return FALSE;
    }

    /**
     * Verifica si el archivo esta subido en el servidor y listo para guardarse
     * 
     * @return boolean
     */
    public function isUploaded()
    {
        // Verifica si ha ocurrido un error al subir
        if ($_FILES[$this->_name]['error'] > 0) {
            $error = array(
                UPLOAD_ERR_INI_SIZE => 'el archivo excede el tamaño máximo (' . ini_get('upload_max_filesize') . 'b) permitido por el servidor',
                UPLOAD_ERR_FORM_SIZE => 'el archivo excede el tamaño máximo permitido',
                UPLOAD_ERR_PARTIAL => 'se ha subido el archivo parcialmente',
                UPLOAD_ERR_NO_FILE => 'no se ha subido ningún archivo',
                UPLOAD_ERR_NO_TMP_DIR => 'no se encuentra el directorio de archivos temporales',
                UPLOAD_ERR_CANT_WRITE => 'falló al escribir el archivo en disco',
                UPLOAD_ERR_EXTENSION => 'una extensión de php ha detenido la subida del archivo'
            );

            Flash::error('Error: ' . $error[$_FILES[$this->_name]['error']]);
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Valida el archivo antes de guardar
     * 
     * @return boolean
     */
    protected function _validates()
    {
        // Denegar subir archivos de scripts ejecutables
        if (!$this->_allowScripts && preg_match('/\.(php|phtml|php3|php4|js|shtml|pl|py|rb|rhtml)$/i', $_FILES[$this->_name]['name'])) {
            Flash::error('Error: no esta permitido subir scripts ejecutables');
            return FALSE;
        }

        // Valida el tipo de archivo
        if ($this->_types !== NULL && !$this->_validatesTypes()) {
            Flash::error('Error: el tipo de archivo no es válido');
            return FALSE;
        }

        // Valida extensión del archivo
        if ($this->_extensions !== NULL && !preg_match('/\.(' . implode('|', $this->_extensions) . ')$/i', $_FILES[$this->_name]['name'])) {
            Flash::error('Error: la extensión del archivo no es válida');
            return FALSE;
        }

        // Verifica si es superior al tamaño indicado
        if ($this->_maxSize !== NULL && $_FILES[$this->_name]['size'] > $this->_toBytes($this->_maxSize)) {
            Flash::error("Error: no se admiten archivos superiores a $this->_maxSize" . 'b');
            return FALSE;
        }

        // Verifica si es inferior al tamaño indicado
        if ($this->_minSize !== NULL && $_FILES[$this->_name]['size'] < $this->_toBytes($this->_minSize)) {
            Flash::error("Error: no se admiten archivos inferiores a $this->_minSize" . 'b');
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Valida que el tipo de archivo
     *
     * @return boolean
     */
    protected function _validatesTypes()
    {
        return in_array($_FILES[$this->_name]['type'], $this->_types);
    }

    /**
     * Devuelve la extensión
     *
     * @return string
     */
    protected function _getExtension()
    {
        if($ext = pathinfo($_FILES[$this->_name]['name'], PATHINFO_EXTENSION)){
            return '.'. $ext;
        }
        return NULL;
    }

    /**
     * Valida si puede sobrescribir el archivo
     *
     * @return boolean
     */
    protected function _overwrite($name)
    {
        if ($this->_overwrite) {
            return TRUE;
        }
        if (file_exists("$this->_path/$name")) {
            Flash::error('Error: ya existe este fichero. Y no se permite reescribirlo');
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Convierte de tamaño legible por humanos a bytes
     *
     * @param string $size
     * @return int
     */
    protected function _toBytes($size)
    {
        if (is_int($size) || ctype_digit($size)) {
            return (int) $size;
        }

        $tipo = strtolower(substr($size, -1));
        $size = (int) $size;

        switch ($tipo) {
            case 'g': //Gigabytes
                $size *= 1073741824;
                break;
            case 'm': //Megabytes
                $size *= 1048576;
                break;
            case 'k': //Kilobytes
                $size *= 1024;
                break;
            default :
                $size = -1;
                Flash::error('Error: el tamaño debe ser un int para bytes, o un string terminado con K, M o G. Ej: 30k , 2M, 2G');
        }

        return $size;
    }

    /**
     * Guardar el archivo en el servidor
     * 
     * @param string $name nombre con el que se guardará el archivo
     * @return boolean
     */
    protected abstract function _saveFile($name);

    /**
     * Obtiene el adaptador para Upload
     *
     * @param string $name nombre de archivo recibido por POST
     * @param string $adapter (file, image, model)
     * @return Upload
     */
    public static function factory($name, $adapter = 'file')
    {
        require_once dirname(__FILE__) . "/adapters/{$adapter}_upload.php";
        $class = $adapter . 'upload';

        return new $class($name);
    }

}
