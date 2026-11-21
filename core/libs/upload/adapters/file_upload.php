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
 * Clase para guardar archivo subido
 *
 * @category   Kumbia
 * @package    Upload
 * @subpackage Adapters
 */
class FileUpload extends Upload
{
    /**
     * Constructor
     *
     * @param string $name nombre de archivo por metodo POST
     */
    public function __construct(string $name)
    {
        parent::__construct($name);

        // Ruta donde se guardara el archivo
        $this->path = dirname($_SERVER['SCRIPT_FILENAME']) . '/files/upload';
    }

    /**
     * Guardar el archivo en el servidor
     *
     * @param string $name nombre con el que se guardará el archivo
     * @return boolean
     */
    protected function saveFile(string $name): bool
    {
        return move_uploaded_file($_FILES[$this->name]['tmp_name'], "$this->path/$name");
    }
}
