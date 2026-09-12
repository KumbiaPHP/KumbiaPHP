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

class FileUtilTest extends PHPUnit\Framework\TestCase
{
    private string $temporaryDirectory;

    protected function setUp(): void
    {
        $this->temporaryDirectory = sys_get_temp_dir() . DIRECTORY_SEPARATOR
            . 'kumbia file util ' . bin2hex(random_bytes(8));
        mkdir($this->temporaryDirectory, 0777, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->temporaryDirectory);
    }

    public static function validRelativePathProvider(): array
    {
        return [
            ['user', 'user'],
            ['admin/user', 'admin/user'],
            ['admin/user/', 'admin/user'],
            ['model-name_2', 'model-name_2'],
        ];
    }

    #[DataProvider('validRelativePathProvider')]
    public function testNormalizeRelativePathAcceptsSafePaths(string $path, string $expected): void
    {
        $this->assertSame($expected, FileUtil::normalizeRelativePath($path));
    }

    public static function unsafeRelativePathProvider(): array
    {
        return [
            [''],
            ['/'],
            ['.'],
            ['..'],
            ['admin/.'],
            ['admin/..'],
            ['admin/../user'],
            ['admin//user'],
            ['\\admin\\user'],
            ['/outside'],
            ['C:/outside'],
            ['c:\\outside'],
            ["admin\0user"],
        ];
    }

    #[DataProvider('unsafeRelativePathProvider')]
    public function testNormalizeRelativePathRejectsUnsafePaths(string $path): void
    {
        $this->expectException(KumbiaException::class);
        $this->expectExceptionMessage('La ruta indicada no es segura');

        FileUtil::normalizeRelativePath($path);
    }

    public function testResolveRelativePathReturnsPathInsideBase(): void
    {
        $base = $this->temporaryDirectory . '/models';
        mkdir($base, 0777, true);

        $this->assertSame(
            $base . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'user.php',
            FileUtil::resolveRelativePath($base, 'admin/user', '.php')
        );
    }

    public function testResolveRelativePathAcceptsBaseWithTrailingSeparator(): void
    {
        $base = $this->temporaryDirectory . '/models';
        mkdir($base, 0777, true);

        $this->assertSame(
            $base . DIRECTORY_SEPARATOR . 'user.php',
            FileUtil::resolveRelativePath($base . DIRECTORY_SEPARATOR, 'user', '.php')
        );
    }

    public function testResolveRelativePathChecksExistingNestedPaths(): void
    {
        $base = $this->temporaryDirectory . '/models';
        mkdir($base . '/admin', 0777, true);
    
        $this->assertSame(
            $base . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'user.php',
            FileUtil::resolveRelativePath($base, 'admin/user', '.php')
        );
    }

    #[DataProvider('unsafeRelativePathProvider')]
    public function testResolveRelativePathRejectsUnsafePaths(string $path): void
    {
        $base = $this->temporaryDirectory . '/models';
        mkdir($base, 0777, true);

        $this->expectException(KumbiaException::class);
        $this->expectExceptionMessage('La ruta indicada no es segura');

        FileUtil::resolveRelativePath($base, $path, '.php');
    }

    public function testResolveRelativePathRejectsExistingSymlinkOutsideBase(): void
    {
        if (!function_exists('symlink')) {
            $this->markTestSkipped('Symbolic links are not available');
        }

        $base = $this->temporaryDirectory . '/models';
        $outside = $this->temporaryDirectory . '/outside';
        mkdir($base, 0777, true);
        mkdir($outside, 0777, true);
        file_put_contents($outside . '/user.php', '<?php');

        if (!@symlink($outside, $base . '/linked')) {
            $this->markTestSkipped('Symbolic links cannot be created');
        }

        $this->expectException(KumbiaException::class);
        $this->expectExceptionMessage('La ruta indicada no es segura');

        FileUtil::resolveRelativePath($base, 'linked/user.php');
    }

    private function removeDirectory(string $path): void
    {
        if (is_link($path) || is_file($path)) {
            unlink($path);
            return;
        }
        if (!is_dir($path)) {
            return;
        }

        foreach (scandir($path) as $entry) {
            if ($entry !== '.' && $entry !== '..') {
                $this->removeDirectory($path . DIRECTORY_SEPARATOR . $entry);
            }
        }
        rmdir($path);
    }
}
