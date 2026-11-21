<?php
/**
 * KumbiaPHP web & app Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @category   Kumbia
 * @package    Upload
 *
 * @copyright  Copyright (c) 2005 - 2026 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Sube archivos al servidor.
 *
 * @category   Kumbia
 * @package    Upload
 */
abstract class Upload {

    /**
     * Nombre de archivo subido por método POST
     */
    protected string $name;

    /**
     * Ruta donde se guardara el archivo
     */
    protected string $path;

    /**
     * Permitir subir archivos de scripts ejecutables
     */
    protected bool $allowScripts = false;

    /**
     * Tamaño mínimo del archivo
     */
    protected int $minSize = 1;

    /**
     * Tamaño máximo del archivo
     */
    protected int $maxSize;

    /**
     * Tipos de archivo permitidos utilizando mime
     */
    protected array $types = [];

    /**
     * Extensión de archivo permitida
     */
    protected array $extensions = [];

    /**
     * Permitir sobrescribir ficheros
     */
    protected bool $overwrite = false;

    /**
     * MIME type real
     */
    public readonly string $realMime;

    /**
     * Basename del archivo subido
     */
    public readonly string $baseName;

    /**
     * Extensión del archivo subido
     */
    public readonly string $extension;

    /**
     * Nombre del archivo guardado (sin ruta)
     */
    public string $savedName = '';

    /**
     * Constructor
     *
     * @param string $name nombre de archivo por método POST
     */
    public function __construct(string $name) {
        $this->name = $name;
        $this->path = dirname($_SERVER['SCRIPT_FILENAME']);
        $this->maxSize = self::toBytes(ini_get('upload_max_filesize'));
        $this->realMime = $this->getExtension();
        $this->baseName = basename($_FILES[$this->name]['name']);
        $this->extension = explode('/', $this->realMime)[1] ?? '';
    }

    /**
     * Devuelve el nombre original enviado por el cliente, o NULL si no es un string.
     * Es metadata no confiable: no es una ruta segura ni el nombre del archivo guardado.
     */
    public function getOriginalName(): ?string {
        $file = $_FILES[$this->name] ?? null;
        if (!is_array($file)) {
            return null;
        }
        $name = $file['name'] ?? null;

        return is_string($name) ? $name : null;
    }

    /**
     * Indica si se permitirá guardar archivos de scripts ejecutables
     */
    public function setAllowScripts(bool $value) {
        $this->allowScripts = $value;
    }

    /**
     * Asigna la ruta al directorio de destino para el archivo
     * 
     *
     * @param string $path ruta al directorio de destino (Ej: /home/usuario/data)
     */
    public function setPath(string $path) {
        $this->path = $path;
    }

    /**
     * Asigna el tamaño mínimo permitido para el archivo
     */
    public function setMinSize(string|int $size) {
        $this->minSize = self::toBytes($size);
    }

    /**
     * Asigna el tamaño máximo permitido para el archivo
     */
    public function setMaxSize(string|int $size) {
        $this->maxSize = self::toBytes($size);
    }

    /**
     * Asigna los tipos de archivos permitido (MIME)
     *
     * @param array|string $value lista de tipos de archivos permitidos (mime) si es string separado por |
     */
    public function setTypes(array|string $value) {
        if (!is_array($value)) {
            $value = explode('|', $value);
        }

        $this->types = $value;
    }

    /**
     * Asigna las extensiones de archivos permitidas
     *
     * @deprecated Usar setTypes() con MIME types, este método no es seguro
     * @param array|string $value lista de extensiones para archivos, si es string separado por |
     */
    public function setExtensions(array|string $value) {
        if (!is_array($value)) {
            $value = explode('|', $value);
        }

        $this->extensions = $value;
    }

    /**
     * Permitir sobrescribir el fichero
     */
    public function overwrite(bool $value) {
        $this->overwrite = $value;
    }

    /**
     * Acciones antes de guardar
     *
     * @param string $name nombre con el que se va a guardar el archivo
     * @return  boolean|null
     */
    protected function beforeSave(string $name) {
    }

    /**
     * Acciones después de guardar
     *
     * @param string $name nombre con el que se guardo el archivo
     * @return  boolean|null
     */
    protected function afterSave(string $name) {
    }

    /**
     * Sanitiza un nombre de archivo para prevenir caracteres peligrosos
     */
    protected function sanitizeName(string $name): string {
        // Elimina rutas absolutas y caracteres peligrosos
        $name = basename($name);
        $name = preg_replace('/[^a-zA-Z0-9._-]/', '_', $name);
        // Evita nombres que comiencen con punto (archivos ocultos)
        if (str_starts_with($name, '.')) {
            $name = '_' . $name;
        }
        return $name;
    }

    /**
     * Devuelve el nombre con el que se guardó el archivo en la última operación
     */
    public function getSavedName(): string {
        return $this->savedName;
    }

    /**
     * Devuelve la ruta completa del archivo guardado
     */
    public function getSavedPath(): string {
        return $this->path . '/' . $this->savedName;
    }

    /**
     * Guarda el archivo subido
     *
     * @param string $name nombre con el que se guardara el archivo (vacío = usar nombre original)
     * @return string|false Nombre de archivo generado con la extensión o FALSE si falla
     */
    public function save(string $name = ''): string|false {
        if (!$this->isUploaded()) {
            return false;
        }

        if (!$name) {
            $name = $this->sanitizeName($this->baseName);
        }

        // Guarda el archivo
        if ($this->beforeSave($name) !== false
            && $this->permitOverwrite($name)
            && $this->validates()
            && $this->saveFile($name)
        ) {
            $this->savedName = $name;
            $this->afterSave($name);
            return $name;
        }
        return false;
    }

    /**
     * Guarda el archivo con un nombre aleatorio
     *
     * @param string $prefix Prefijo opcional para el nombre generado
     * @return string|false Nombre de archivo generado o FALSE si falla
     */
    public function saveRandom(string $prefix = ''): string|false {
        $random = bin2hex(random_bytes(16));
        $name = $prefix !== '' ? $prefix . '_' . $random : $random;

        return $this->save($name);
    }

    /**
     * Verifica si el archivo esta subido en el servidor y listo para guardarse
     */
    public function isUploaded(): bool {
        $file = $_FILES[$this->name] ?? null;

        // Verifica que exista la entrada de $_FILES
        if (!is_array($file)) {
            Flash::error('No se ha recibido ningún archivo');
            return false;
        }

        // Verifica si ha ocurrido un error al subir
        if ($file['error'] > 0) {
            $error = [
                UPLOAD_ERR_INI_SIZE => 'El archivo excede el tamaño máximo (' . ini_get('upload_max_filesize') . 'b) permitido por el servidor',
                UPLOAD_ERR_FORM_SIZE => 'El archivo excede el tamaño máximo permitido',
                UPLOAD_ERR_PARTIAL => 'Se ha subido el archivo parcialmente',
                UPLOAD_ERR_NO_FILE => 'No se ha subido ningún archivo',
                UPLOAD_ERR_NO_TMP_DIR => 'No se encuentra el directorio de archivos temporales',
                UPLOAD_ERR_CANT_WRITE => 'Falló al escribir el archivo en disco',
                UPLOAD_ERR_EXTENSION => 'Una extensión de php ha detenido la subida del archivo',
            ];

            Flash::error($error[$file['error']] ?? 'desconocido');
            return false;
        }

        // Verifica que sea un archivo subido vía HTTP
        if (!is_uploaded_file($file['tmp_name'])) {
            Flash::error('El archivo no fue subido vía HTTP');
            return false;
        }

        return true;
    }

    /**
     * Valida el archivo antes de guardar
     */
    protected function validates(): bool {
        // Verifica que se pueda escribir en el directorio
        if (!is_dir($this->path) || !is_writable($this->path)) {
            Flash::error('No se puede escribir en el directorio o no es un directorio válido');
            return false;
        }

        $validations = ['allowScripts', 'types', 'extensions', 'maxSize', 'minSize'];
        foreach ($validations as $val) {
            if (!$this->$val()) {
                return false;
            }
        }
        return true;
    }

    /**
     * Devuelve la extensión
     */
    protected function getExtension(): string {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $realMime = $finfo->file($_FILES[$this->name]['tmp_name']);
        return $realMime === false ? '' : $realMime;
    }

    /**
     * Valida si puede sobrescribir el archivo
     *
     * @return boolean
     */
    protected function permitOverwrite(string $name): bool {
        if ($this->overwrite) {
            return true;
        }
        if (is_file("$this->path/$name")) {
            Flash::error('Ya existe este fichero. Y no se permite reescribirlo');
            return false;
        }
        return true;
    }

    /**
     * Convierte de tamaño legible por humanos a bytes
     */
    protected static function toBytes(string|int $size): int {
        if (is_int($size) || ctype_digit($size)) {
            return (int) $size;
        }

        $tipo = strtoupper(substr($size, -1));
        $size = (int) $size;

        match($tipo) {
            'G' => $size *= 1073741824, // Gigabytes
            'M' => $size *= 1048576,    // Megabytes
            'K' => $size *= 1024,       // Kilobytes
            default => $size = -1,
        };

        if ($size === -1) {
            Flash::error('El tamaño debe ser un int para bytes, o un string terminado con K, M o G. Ej: 30K , 2M, 2G');
        }

        return $size;
    }

    /**
     * Guardar el archivo en el servidor
     *
     * @param string $name nombre con el que se guardará el archivo
     */
    protected abstract function saveFile(string $name): bool;

    /**
     * Obtiene el adaptador para Upload
     *
     * @param string $name nombre de archivo recibido por POST
     * @param string $adapter (file, image, model)
     * @return Upload
     */
    public static function factory(string $name, string $adapter = 'file'): static {
        require_once __DIR__ . "/adapters/{$adapter}_upload.php";
        $class = $adapter . 'Upload';

        return new $class($name);
    }

    /**
     * Valida una condición y muestra un mensaje de error si es verdadera
     *
     * @param bool $cond Condición a evaluar
     * @param string $message Mensaje de error a mostrar si la condición es verdadera
     * @return bool Retorna false si la condición es verdadera, true en caso contrario
     */
    protected function cond(bool $cond, string $message): bool {
        if ($cond) {
            Flash::error($message);
            return false;
        }
        return true;
    }

    protected function allowScripts(): bool {
        return $this->cond(
            !$this->allowScripts && preg_match('/\.(php|phtml|php3|php4|js|shtml|pl|py|rb|rhtml)$/i', $_FILES[$this->name]['name']),
            'No está permitido subir scripts ejecutables'
        );
    }

    /**
     * Valida que el tipo de archivo
     *
     * @return boolean
     */
    protected function types(): bool {
        // Si no hay tipos configurados, se permite todo
        if ($this->types === []) {
            return true;
        }

        // Compara el MIME completo (image/jpeg), no solo la extensión
        return $this->cond(
            !in_array($this->realMime, $this->types),
            'El tipo de archivo no es válido'
        );
    }

    protected function extensions(): bool {
        return $this->cond(
            !($this->extensions === []) && !in_array($this->extension, $this->extensions),
            'La extensión del archivo no es válida'
        );
    }

    protected function maxSize(): bool {
        return $this->cond(
            $_FILES[$this->name]['size'] > $this->maxSize,
            "No se admiten archivos superiores a $this->maxSize b"
        );
    }

    protected function minSize(): bool {
        return $this->cond(
            $_FILES[$this->name]['size'] < $this->minSize,
            "No se admiten archivos inferiores a $this->minSize b"
        );
    }
}
