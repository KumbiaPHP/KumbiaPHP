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
 * @subpackage   Adapters
 *
 * @copyright  Copyright (c) 2005 - 2026 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

require_once __DIR__ . '/../UploadHelpers.php';

function _createFileUpload(): FileUpload {
    return new class('document') extends FileUpload {
        public function __construct(string $name) {
            $tmpFile = sys_get_temp_dir() . '/upload_test_plain.txt';
            file_put_contents($tmpFile, 'test content');
            global $_tmpFiles;
            $_tmpFiles[] = $tmpFile;

            $_FILES = [
                $name => [
                    'name' => 'file.txt',
                    'type' => 'text/plain',
                    'tmp_name' => $tmpFile,
                    'size' => 12,
                    'error' => 0,
                ],
            ];

            $_SERVER['SCRIPT_FILENAME'] = sys_get_temp_dir() . '/index.php';
            parent::__construct($name);

            if (!is_dir($this->path)) {
                mkdir($this->path, 0755, true);
            }
        }
        public function isUploaded(): bool { return true; }
        protected function saveFile(string $name): bool {
            return copy($_FILES[$this->name]['tmp_name'], "$this->path/$name");
        }
    };
}

describe('factory', function () {
    it('creates file adapter via factory', function () {
        $tmpFile = sys_get_temp_dir() . '/upload_test_factory.txt';
        file_put_contents($tmpFile, 'test');
        global $_tmpFiles;
        $_tmpFiles[] = $tmpFile;

        $_FILES = ['document' => ['name' => 'file.txt', 'type' => 'text/plain', 'tmp_name' => $tmpFile, 'size' => 4, 'error' => 0]];

        $upload = Upload::factory('document', 'file');
        expect($upload)->toBeInstanceOf(FileUpload::class);
    });
});

describe('path', function () {
    it('has default path ending with files/upload', function () {
        $upload = _createFileUpload();
        expect(_getProperty($upload, 'path'))->toMatch('#/files/upload$#');
    });

    it('allows changing default path with setPath', function () {
        $upload = _createFileUpload();
        $upload->setPath('/custom/upload/path');
        expect(_getProperty($upload, 'path'))->toBe('/custom/upload/path');
    });
});

describe('saveFile', function () {
    it('returns true when saveFile succeeds', function () {
        $upload = _createFileUpload();
        $dir = dirname(_getProperty($upload, 'path'));
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        expect(_getMethod($upload, 'validates'))->toBeTrue();

        $result = $upload->save('test.txt');
        expect($result)->toBe('test.txt');

        if (file_exists(_getProperty($upload, 'path') . '/test.txt')) {
            unlink(_getProperty($upload, 'path') . '/test.txt');
        }
    });

    it('returns false when saveFile fails', function () {
        $upload = _createFileUpload();
        $upload->setPath('/no/existe/esta/ruta');

        expect(_getMethod($upload, 'validates'))->toBeFalse();

        $result = $upload->save('test.txt');
        expect($result)->toBeFalse();
    });

    it('saves file with custom name', function () {
        $upload = _createFileUpload();
        $dir = dirname(_getProperty($upload, 'path'));
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $customName = 'mi_archivo_personalizado.txt';

        $result = $upload->save($customName);
        expect($result)->toBe($customName);
        expect(_getProperty($upload, 'savedName'))->toBe($customName);

        if (file_exists(_getProperty($upload, 'path') . '/' . $customName)) {
            unlink(_getProperty($upload, 'path') . '/' . $customName);
        }
    });

    it('saves file in the configured path', function () {
        $upload = _createFileUpload();
        $customPath = sys_get_temp_dir() . '/upload_test_custom';
        $upload->setPath($customPath);
        if (!is_dir($customPath)) {
            mkdir($customPath, 0755, true);
        }

        $upload->save('test.txt');

        expect(file_exists($customPath . '/test.txt'))->toBeTrue();

        if (file_exists($customPath . '/test.txt')) {
            unlink($customPath . '/test.txt');
        }
        if (is_dir($customPath)) {
            rmdir($customPath);
        }
    });

    it('uses original name when save is called without arguments', function () {
        $upload = _createFileUpload();
        $dir = dirname(_getProperty($upload, 'path'));
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $result = $upload->save();

        expect($result)->toBe('file.txt');

        if (file_exists(_getProperty($upload, 'path') . '/file.txt')) {
            unlink(_getProperty($upload, 'path') . '/file.txt');
        }
    });
});
