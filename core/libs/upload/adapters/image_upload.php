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
 * @subpackage Adapters
 *
 * @copyright  Copyright (c) 2005 - 2026 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Clase para guardar imagen subida
 *
 * @category   Kumbia
 * @package    Upload
 * @subpackage Adapters
 */
class ImageUpload extends Upload
{
    /**
     * Información de la imagen
     */
    protected array|false $imgInfo;
    /**
     * Ancho mínimo de la imagen en px
     */
    protected ?int $minWidth = null;
    /**
     * Ancho máximo de la imagen en px
     */
    protected ?int $maxWidth = null;
    /**
     * Alto mínimo de la imagen en px
     */
    protected ?int $minHeight = null;
    /**
     * Alto máximo de la imagen en px
     */
    protected ?int $maxHeight = null;

    /**
     * Constructor
     *
     * @param string $name nombre de archivo por metodo POST
     */
    public function __construct(string $name)
    {
        parent::__construct($name);

        $this->imgInfo = getimagesize($_FILES[$name]['tmp_name']);

        // Ruta donde se guardara el archivo
        $this->path = dirname($_SERVER['SCRIPT_FILENAME']) . '/img/upload';
    }

    /**
     * Asigna el ancho mínimo de la imagen en px
     */
    public function setMinWidth(int $value)
    {
        $this->minWidth = $value;
    }

    /**
     * Asigna el ancho máximo de la imagen en px
     */
    public function setMaxWidth(int $value)
    {
        $this->maxWidth = $value;
    }

    /**
     * Asigna el alto mínimo de la imagen en px
     */
    public function setMinHeight(int $value)
    {
        $this->minHeight = $value;
    }

    /**
     * Asigna el alto máximo de la imagen en px
     */
    public function setMaxHeight(int $value)
    {
        $this->maxHeight = $value;
    }

    /**
     * Valida el archivo antes de guardar
     *
     * @throws RuntimeException Si se intenta validar sin una imagen válida
     */
    protected function validates(): bool
    {
        // Verifica que la imagen se haya cargado correctamente
        if ($this->imgInfo === false) {
            Flash::error('El archivo debe ser una imagen válida');
            return false;
        }

        // Verifica que sea un tipo de imagen reconocido
        if (!str_starts_with($this->realMime, 'image/')) {
            Flash::error('El archivo debe ser una imagen');
            return false;
        }

        // Validaciones de dimensiones
        $dimensions = [
            ['label' => 'ancho', 'value' => $this->imgInfo[0], 'min' => $this->minWidth, 'max' => $this->maxWidth],
            ['label' => 'alto', 'value' => $this->imgInfo[1], 'min' => $this->minHeight, 'max' => $this->maxHeight],
        ];

        foreach ($dimensions as $dim) {
            if ($dim['min'] !== null && $dim['value'] < $dim['min']) {
                Flash::error("El {$dim['label']} de la imagen debe ser >= {$dim['min']}px");
                return false;
            }
            if ($dim['max'] !== null && $dim['value'] > $dim['max']) {
                Flash::error("El {$dim['label']} de la imagen debe ser <= {$dim['max']}px");
                return false;
            }
        }

        return parent::validates();
    }

    /**
     * Guardar el archivo en el servidor
     *
     * @param string $name nombre con el que se guardará el archivo
     */
    protected function saveFile(string $name): bool
    {
        return move_uploaded_file($_FILES[$this->name]['tmp_name'], "$this->path/$name");
    }
}
