<?php
/**
 * KumbiaPHP web & app Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @category   Test
 * @package    Upload
 *
 * @copyright  Copyright (c) 2005 - 2026 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

$_filesBackup = null;
$_tmpFiles = [];

beforeEach(function () {
    global $_filesBackup, $_tmpFiles;
    $_filesBackup = $_FILES;
    $_tmpFiles = [];
    $_FILES = [];
});

afterEach(function () {
    global $_filesBackup, $_tmpFiles;
    foreach ($_tmpFiles as $tmpFile) {
        if (file_exists($tmpFile)) {
            unlink($tmpFile);
        }
    }
    $_FILES = $_filesBackup;
});

function _createTmpFile(string $content = 'test content', string $name = 'file.txt'): string {
    $tmpFile = sys_get_temp_dir() . '/' . uniqid('upload_test_', true) . '_' . basename($name);
    file_put_contents($tmpFile, $content);
    global $_tmpFiles;
    $_tmpFiles[] = $tmpFile;
    return $tmpFile;
}

function _createUpload(string $name = 'document'): Upload {
    return new class($name) extends Upload {
        protected function saveFile(string $name): bool { return false; }
        public function isUploaded(): bool { return true; }
    };
}

function _buildFiles(string $name, string $content = 'test content', string $fileName = 'file.txt', ?int $size = null): array {
    $tmpFile = _createTmpFile($content, $fileName);
    $fileSize = $size ?? strlen($content);
    return [
        $name => [
            'name' => $fileName,
            'type' => mime_content_type($tmpFile),
            'tmp_name' => $tmpFile,
            'size' => $fileSize,
            'error' => 0,
        ],
    ];
}

function _createImageUpload(string $name = 'document'): ImageUpload {
    $pngBase64 = base64_encode(
        "\x89PNG\x0d\x0a\x1a\x0a\x00\x00\x00\x0dIHDR\x00\x00\x00\x01\x00\x00\x00\x01\x08\x02\x00\x00\x00\x90wS\xde\x00\x00\x00\x0cIDATx\x9cc\xf8\x0f\x00\x00\x01\x01\x00\x05\x18\xd8N\x00\x00\x00\x00IEND\xaeB\x60\x82"
    );
    $tmpFile = _createTmpFile(base64_decode($pngBase64), 'image.png');

    $_FILES = [
        $name => [
            'name' => 'image.png',
            'type' => 'image/png',
            'tmp_name' => $tmpFile,
            'size' => strlen(file_get_contents($tmpFile)),
            'error' => 0,
        ],
    ];

    $_SERVER['SCRIPT_FILENAME'] = sys_get_temp_dir() . '/index.php';

    $upload = new class($name) extends ImageUpload {
        public function __construct(string $name) { parent::__construct($name); }
        public function isUploaded(): bool { return true; }
        protected function saveFile(string $name): bool {
            return copy($_FILES[$this->name]['tmp_name'], "$this->path/$name");
        }
    };

    // Crear directorio de destino
    $uploadPath = _getProperty($upload, 'path');
    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0755, true);
    }

    return $upload;
}

function _createInvalidImageUpload(string $name = 'document'): ImageUpload {
    $tmpFile = _createTmpFile('not an image', 'fake.png');

    $_FILES = [
        $name => [
            'name' => 'fake.png',
            'type' => 'image/png',
            'tmp_name' => $tmpFile,
            'size' => strlen(file_get_contents($tmpFile)),
            'error' => 0,
        ],
    ];

    return new class($name) extends ImageUpload {
        public function __construct(string $name) { parent::__construct($name); }
    };
}
