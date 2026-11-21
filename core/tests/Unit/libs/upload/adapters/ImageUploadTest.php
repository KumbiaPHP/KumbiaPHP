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

describe('factory', function () {
    it('creates image adapter via factory', function () {
        $pngBase64 = base64_encode(
            "\x89PNG\x0d\x0a\x1a\x0a\x00\x00\x00\x0dIHDR\x00\x00\x00\x01\x00\x00\x00\x01\x08\x02\x00\x00\x00\x90wS\xde\x00\x00\x00\x0cIDATx\x9cc\xf8\x0f\x00\x00\x01\x01\x00\x05\x18\xd8N\x00\x00\x00\x00IEND\xaeB\x60\x82"
        );
        $tmpFile = _createTmpFile(base64_decode($pngBase64), 'image.png');
        $_FILES = ['document' => ['name' => 'image.png', 'type' => 'image/png', 'tmp_name' => $tmpFile, 'size' => strlen(file_get_contents($tmpFile)), 'error' => 0]];

        $upload = Upload::factory('document', 'image');
        expect($upload)->toBeInstanceOf(ImageUpload::class);
    });
});

describe('path', function () {
    it('has default path ending with img/upload', function () {
        $upload = _createImageUpload();
        expect(_getProperty($upload, 'path'))->toMatch('#/img/upload$#');
    });

    it('allows changing default path with setPath', function () {
        $upload = _createImageUpload();
        $upload->setPath('/custom/images/path');
        expect(_getProperty($upload, 'path'))->toBe('/custom/images/path');
    });
});

describe('imgInfo', function () {
    it('stores image info from getimagesize', function () {
        $upload = _createImageUpload();

        $imgInfo = _getProperty($upload, 'imgInfo');

        expect($imgInfo)->not()->toBeFalse();
        expect($imgInfo[0])->toBe(1);
        expect($imgInfo[1])->toBe(1);
    });

    it('returns false for imgInfo when image is invalid', function () {
        $upload = _createInvalidImageUpload();

        expect(_getProperty($upload, 'imgInfo'))->toBeFalse();
    });
});

describe('dimension setters', function () {
    it('sets minimum width', function () {
        $upload = _createImageUpload();
        $upload->setMinWidth(100);

        expect(_getProperty($upload, 'minWidth'))->toBe(100);
    });

    it('sets maximum width', function () {
        $upload = _createImageUpload();
        $upload->setMaxWidth(800);

        expect(_getProperty($upload, 'maxWidth'))->toBe(800);
    });

    it('sets minimum height', function () {
        $upload = _createImageUpload();
        $upload->setMinHeight(50);

        expect(_getProperty($upload, 'minHeight'))->toBe(50);
    });

    it('sets maximum height', function () {
        $upload = _createImageUpload();
        $upload->setMaxHeight(600);

        expect(_getProperty($upload, 'maxHeight'))->toBe(600);
    });
});

describe('validates', function () {
    it('returns false when imgInfo is false', function () {
        $upload = _createInvalidImageUpload();

        expect(_getMethod($upload, 'validates'))->toBeFalse();
    });

    it('returns true when image is valid and no dimension constraints', function () {
        $upload = _createImageUpload();

        expect(_getMethod($upload, 'validates'))->toBeTrue();
    });

    it('returns false when width is below minimum', function () {
        $upload = _createImageUpload();
        $upload->setMinWidth(100);

        expect(_getMethod($upload, 'validates'))->toBeFalse();
    });

    it('returns false when height is below minimum', function () {
        $upload = _createImageUpload();
        $upload->setMinHeight(100);

        expect(_getMethod($upload, 'validates'))->toBeFalse();
    });
});

describe('saveFile', function () {
    it('saves image file successfully', function () {
        $upload = _createImageUpload();
        $customPath = sys_get_temp_dir() . '/upload_test_images';
        $upload->setPath($customPath);
        if (!is_dir($customPath)) {
            mkdir($customPath, 0755, true);
        }

        $result = $upload->save('test_image.png');
        expect($result)->toBe('test_image.png');
        expect(file_exists($customPath . '/test_image.png'))->toBeTrue();

        if (file_exists($customPath . '/test_image.png')) {
            unlink($customPath . '/test_image.png');
        }
        if (is_dir($customPath)) {
            rmdir($customPath);
        }
    });

    it('rejects invalid image file before saving', function () {
        $upload = _createInvalidImageUpload();
        $customPath = sys_get_temp_dir() . '/upload_test_invalid';
        $upload->setPath($customPath);

        $result = $upload->save('invalid.png');
        expect($result)->toBeFalse();
    });

    it('saves image with custom name', function () {
        $upload = _createImageUpload();
        $customPath = sys_get_temp_dir() . '/upload_test_custom_name';
        $upload->setPath($customPath);
        if (!is_dir($customPath)) {
            mkdir($customPath, 0755, true);
        }

        $customName = 'mi_imagen_personalizada.png';
        $result = $upload->save($customName);

        expect($result)->toBe($customName);
        expect(_getProperty($upload, 'savedName'))->toBe($customName);
        expect(file_exists($customPath . '/' . $customName))->toBeTrue();

        if (file_exists($customPath . '/' . $customName)) {
            unlink($customPath . '/' . $customName);
        }
        if (is_dir($customPath)) {
            rmdir($customPath);
        }
    });

    it('uses original name when save is called without arguments', function () {
        $upload = _createImageUpload();
        $result = $upload->save();

        expect($result)->toBe('image.png');

        $path = _getProperty($upload, 'path') . '/image.png';
        if (file_exists($path)) {
            unlink($path);
        }
    });
});
