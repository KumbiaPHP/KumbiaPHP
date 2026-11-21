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

require_once __DIR__ . '/UploadHelpers.php';

describe('getOriginalName', function () {
    it('returns original name from $_FILES', function () {
        $_FILES = _buildFiles('document', 'test content', 'report.pdf');

        $upload = _createUpload();

        expect($upload->getOriginalName())->toBe('report.pdf');
    });

    it('returns null when $_FILES field is missing', function () {
        $tmpFile = _createTmpFile();
        $_FILES = ['document' => ['tmp_name' => $tmpFile, 'size' => strlen(file_get_contents($tmpFile)), 'error' => 0]];

        $upload = _createUpload();

        expect($upload->getOriginalName())->toBeNull();
    });

    it('returns null when name key is missing', function () {
        $tmpFile = _createTmpFile();
        $_FILES = ['document' => ['tmp_name' => $tmpFile, 'size' => strlen(file_get_contents($tmpFile)), 'error' => 0]];

        $upload = _createUpload();

        expect($upload->getOriginalName())->toBeNull();
    });

    it('returns null when name is not a string', function () {
        $_FILES = _buildFiles('document', 'test content', 'file.txt');
        $_FILES['document']['name'] = 123;

        $upload = _createUpload();

        expect($upload->getOriginalName())->toBeNull();
    });
});

describe('allowScripts', function () {
    it('allows executable scripts when allowScripts is true', function () {
        $_FILES = _buildFiles('document', '<?php echo 1;', 'evil.php');

        $upload = _createUpload();

        $upload->setAllowScripts(true);
        expect(_getMethod($upload, 'allowScripts'))->toBeTrue();
    });

    it('blocks executable scripts when allowScripts is false', function () {
        $_FILES = _buildFiles('document', '<?php echo 1;', 'evil.php');

        $upload = _createUpload();

        expect(_getMethod($upload, 'allowScripts'))->toBeFalse();
    });

    it('blocks multiple executable script extensions', function (string $filename): void {
        $_FILES = _buildFiles('document', '<?php echo 1;', $filename);

        $upload = _createUpload();

        expect(_getMethod($upload, 'allowScripts'))->toBeFalse();
    })->with([
        'script.phtml',
        'script.php3',
        'script.php4',
        'script.js',
        'script.shtml',
        'script.pl',
        'script.py',
        'script.rb',
        'script.rhtml',
    ]);

    it('allows non-executable script extensions by default', function (string $filename): void {
        $_FILES = _buildFiles('document', 'some content', $filename);

        $upload = _createUpload();

        expect(_getMethod($upload, 'allowScripts'))->toBeTrue();
    })->with([
        'script.txt',
        'document.doc',
        'image.png',
        'style.css',
        'video.mp4',
    ]);
});

describe('types', function () {
    it('allows all types when types array is empty', function () {
        $_FILES = _buildFiles('document', 'test', 'file.xyz');

        $upload = _createUpload();

        expect(_getMethod($upload, 'types'))->toBeTrue();
    });

    it('rejects file when real mime does not match allowed types', function () {
        $_FILES = _buildFiles('document', 'test', 'file.xyz');

        $upload = _createUpload();

        $upload->setTypes(['image/png']);
        expect(_getMethod($upload, 'types'))->toBeFalse();
    });

    it('accepts file when real mime matches allowed types', function () {
        $pngBase64 = base64_encode(
            "\x89PNG\x0d\x0a\x1a\x0a\x00\x00\x00\x0dIHDR\x00\x00\x00\x01\x00\x00\x00\x01\x08\x02\x00\x00\x00\x90wS\xde\x00\x00\x00\x0cIDATx\x9cc\xf8\x0f\x00\x00\x01\x01\x00\x05\x18\xd8N\x00\x00\x00\x00IEND\xaeB\x60\x82"
        );
        $tmpFile = _createTmpFile(base64_decode($pngBase64), 'image.png');
        $_FILES = ['document' => ['name' => 'image.png', 'type' => 'image/png', 'tmp_name' => $tmpFile, 'size' => strlen(file_get_contents($tmpFile)), 'error' => 0]];

        $upload = _createUpload();

        $upload->setTypes(['image/png']);
        expect(_getMethod($upload, 'types'))->toBeTrue();
    });
});

describe('extensions', function () {
    it('rejects file when extension does not match allowed extensions', function () {
        $_FILES = _buildFiles('document', 'test', 'file.xyz');

        $upload = _createUpload();

        $upload->setExtensions(['txt']);
        expect(_getMethod($upload, 'extensions'))->toBeFalse();
    });

    it('accepts file when extension matches allowed extensions', function () {
        $_FILES = _buildFiles('document', 'test', 'file.txt');

        $upload = _createUpload();

        // La extensión se deriva del MIME real (text/plain -> 'plain'), no del nombre del archivo
        $upload->setExtensions(['plain']);
        expect(_getMethod($upload, 'extensions'))->toBeTrue();
    });

    it('allows all extensions when extensions array is empty', function () {
        $_FILES = _buildFiles('document', 'test', 'file.xyz');

        $upload = _createUpload();

        expect(_getMethod($upload, 'extensions'))->toBeTrue();
    });
});

describe('maxSize', function () {
    it('rejects file when size exceeds maximum', function () {
        $_FILES = _buildFiles('document', 'test', 'file.txt');

        $upload = _createUpload();

        $upload->setMaxSize('100b');
        expect(_getMethod($upload, 'maxSize'))->toBeFalse();
    });

    it('accepts file when size is within maximum', function () {
        $_FILES = _buildFiles('document', 'test', 'file.txt');

        $upload = _createUpload();

        $upload->setMaxSize('10K');
        expect(_getMethod($upload, 'maxSize'))->toBeTrue();
    });
});

describe('minSize', function () {
    it('rejects file when size is below minimum', function () {
        $_FILES = _buildFiles('document', 'test', 'file.txt');

        $upload = _createUpload();

        $upload->setMinSize('10K');
        expect(_getMethod($upload, 'minSize'))->toBeFalse();
    });

    it('accepts file when size is above minimum', function () {
        $_FILES = _buildFiles('document', 'test', 'file.txt');

        $upload = _createUpload();

        $upload->setMinSize('100b');
        expect(_getMethod($upload, 'minSize'))->toBeTrue();
    });
});

describe('toBytes', function () {
    it('converts K suffix to bytes', function () {
        $tmpFile = _createTmpFile();
        $_FILES = ['document' => ['name' => 'file.txt', 'tmp_name' => $tmpFile, 'size' => strlen(file_get_contents($tmpFile)), 'error' => 0]];

        $upload = _createUpload();
        expect(_getMethod($upload, 'toBytes', '1K'))->toBe(1024);
    });

    it('converts M suffix to bytes', function () {
        $tmpFile = _createTmpFile();
        $_FILES = ['document' => ['name' => 'file.txt', 'tmp_name' => $tmpFile, 'size' => strlen(file_get_contents($tmpFile)), 'error' => 0]];

        $upload = _createUpload();
        expect(_getMethod($upload, 'toBytes', '2M'))->toBe(2097152);
    });

    it('converts G suffix to bytes', function () {
        $tmpFile = _createTmpFile();
        $_FILES = ['document' => ['name' => 'file.txt', 'tmp_name' => $tmpFile, 'size' => strlen(file_get_contents($tmpFile)), 'error' => 0]];

        $upload = _createUpload();
        expect(_getMethod($upload, 'toBytes', '1G'))->toBe(1073741824);
    });

    it('converts integer bytes directly', function () {
        $tmpFile = _createTmpFile();
        $_FILES = ['document' => ['name' => 'file.txt', 'tmp_name' => $tmpFile, 'size' => strlen(file_get_contents($tmpFile)), 'error' => 0]];

        $upload = _createUpload();
        expect(_getMethod($upload, 'toBytes', 4096))->toBe(4096);
    });

    it('converts numeric string bytes directly', function () {
        $tmpFile = _createTmpFile();
        $_FILES = ['document' => ['name' => 'file.txt', 'tmp_name' => $tmpFile, 'size' => strlen(file_get_contents($tmpFile)), 'error' => 0]];

        $upload = _createUpload();
        expect(_getMethod($upload, 'toBytes', '8192'))->toBe(8192);
    });
});

describe('setters', function () {
    it('sets custom path', function () {
        $_FILES = _buildFiles('document', 'test', 'file.txt');

        $upload = _createUpload();

        $upload->setPath('/custom/path');
        expect(_getProperty($upload, 'path'))->toBe('/custom/path');
    });

    it('sets custom min size', function () {
        $_FILES = _buildFiles('document', 'test', 'file.txt');

        $upload = _createUpload();

        $upload->setMinSize('5K');
        expect(_getProperty($upload, 'minSize'))->toBe(5120);
    });
});

describe('factory', function () {
    it('creates file adapter via factory', function () {
        $_FILES = _buildFiles('document', 'test', 'file.txt');

        $upload = Upload::factory('document', 'file');
        expect($upload)->toBeInstanceOf(FileUpload::class);
    });

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

describe('save', function () {
    it('sets saved name after save', function () {
        $tmpFile = _createTmpFile('test', 'file.txt');
        $_FILES = ['document' => ['name' => 'file.txt', 'type' => 'text/plain', 'tmp_name' => $tmpFile, 'size' => strlen(file_get_contents($tmpFile)), 'error' => 0]];

        $upload = new class('document') extends Upload {
            public function isUploaded(): bool { return true; }
            protected function saveFile(string $name): bool { return true; }
        };

        $result = $upload->save('custom_name.txt');
        expect($result)->toBe('custom_name.txt');
        expect($upload->savedName)->toBe('custom_name.txt');
    });
});
