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
 * @package    Core
 *
 * @copyright  Copyright (c) 2005 - 2026 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

use PHPUnit\Framework\Attributes\DataProvider;

class UploadTest extends PHPUnit\Framework\TestCase
{
    private array $originalFiles;

    protected function setUp(): void
    {
        $this->originalFiles = $_FILES;
        $_FILES = [];
    }

    protected function tearDown(): void
    {
        $_FILES = $this->originalFiles;
    }

    public static function originalNameProvider(): array
    {
        return [
            'selected field' => [
                [
                    'document' => ['name' => 'Client Report.PDF', 'tmp_name' => '/tmp/php-upload'],
                    'other' => ['name' => 'other.txt'],
                ],
                'Client Report.PDF',
            ],
            'empty string' => [['document' => ['name' => '']], ''],
            'missing field' => [['other' => ['name' => 'other.txt']], null],
            'missing name' => [['document' => ['tmp_name' => '/tmp/php-upload']], null],
            'array name' => [['document' => ['name' => ['first.txt', 'second.txt']]], null],
            'null name' => [['document' => ['name' => null]], null],
            'integer name' => [['document' => ['name' => 123]], null],
            'boolean name' => [['document' => ['name' => false]], null],
            'float name' => [['document' => ['name' => 1.5]], null],
            'object name' => [['document' => ['name' => new stdClass()]], null],
            'scalar field' => [['document' => 'invalid'], null],
            'null field' => [['document' => null], null],
            'object field' => [['document' => new stdClass()], null],
        ];
    }

    #[DataProvider('originalNameProvider')]
    public function testGetOriginalNameReturnsOnlyOriginalStringMetadata(array $files, ?string $expected): void
    {
        $_FILES = $files;
        $upload = new class('document') extends Upload {
            protected function _saveFile($name)
            {
                return false;
            }
        };

        $this->assertSame($expected, $upload->getOriginalName());
    }
}
