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

class ConsoleTest extends PHPUnit\Framework\TestCase
{
    private $consoleEntrypoint;
    private $temporaryDirectory;

    protected function setUp(): void
    {
        $this->consoleEntrypoint = dirname(__DIR__, 3) . '/console/kumbia.php';
        $this->temporaryDirectory = sys_get_temp_dir() . DIRECTORY_SEPARATOR
            . 'kumbia console ' . bin2hex(random_bytes(8));

        if (!mkdir($this->temporaryDirectory, 0777, true)) {
            $this->fail('Unable to create the temporary test directory');
        }
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->temporaryDirectory);
    }

    public function testDirectAppPathRemainsAccepted(): void
    {
        $app = $this->temporaryDirectory . '/custom-app';
        $this->createApp($app);

        $result = $this->runConsole($app);

        $this->assertSuccessfulPath($result, $app);
    }

    public function testCurrentDirectoryRemainsAcceptedAsDirectApp(): void
    {
        $app = $this->temporaryDirectory . '/current-app';
        $this->createApp($app);

        $result = $this->runConsole(null, $app);

        $this->assertSuccessfulPath($result, $app);
    }

    public function testProjectRootResolvesDefaultApp(): void
    {
        $root = $this->temporaryDirectory . '/project';
        $app = $root . '/default/app';
        $this->createApp($app);

        $result = $this->runConsole($root);

        $this->assertSuccessfulPath($result, $app);
    }

    public function testDirectAppWinsWhenProjectRootFormAlsoExists(): void
    {
        $app = $this->temporaryDirectory . '/both';
        $this->createApp($app);
        $this->createApp($app . '/default/app');

        $result = $this->runConsole($app);

        $this->assertSuccessfulPath($result, $app);
    }

    #[DataProvider('configFileProvider')]
    public function testConfigFileIdentifiesAnApp(string $configFile): void
    {
        $app = $this->temporaryDirectory . '/config-app';
        $this->createApp($app, $configFile);

        $result = $this->runConsole($app);

        $this->assertSuccessfulPath($result, $app);
    }

    public static function configFileProvider(): array
    {
        return [
            ['config.php'],
            ['config.ini'],
        ];
    }

    public function testConfiguredProductionValueIsPreserved(): void
    {
        $app = $this->temporaryDirectory . '/configured-production-app';
        $this->createApp($app);
        file_put_contents(
            $app . '/config/config.php',
            "<?php\nreturn ['application' => ['production' => 'configured']];\n"
        );

        $result = $this->runConsole($app, null, 'production');

        $this->assertSame('', $result['stderr']);
        $this->assertSame(0, $result['status']);
        $this->assertSame('configured', $result['stdout']);
    }

    #[DataProvider('trailingSeparatorProvider')]
    public function testAcceptedPathHasOnePlatformSeparator(string $separator): void
    {
        $app = $this->temporaryDirectory . '/trailing-app';
        $this->createApp($app);

        $result = $this->runConsole($app . $separator);

        $this->assertSuccessfulPath($result, $app);
    }

    public static function trailingSeparatorProvider(): array
    {
        return [
            ['/'],
            ['\\'],
        ];
    }

    public function testMinimalAppWithoutMvcDirectoriesIsAccepted(): void
    {
        $app = $this->temporaryDirectory . '/minimal-app';
        $this->createApp($app);

        $this->assertDirectoryDoesNotExist($app . '/controllers');
        $this->assertDirectoryDoesNotExist($app . '/models');
        $this->assertDirectoryDoesNotExist($app . '/views');

        $result = $this->runConsole($app);

        $this->assertSuccessfulPath($result, $app);
    }

    public function testSymlinkedAppResolvesCanonically(): void
    {
        $app = $this->temporaryDirectory . '/real-app';
        $link = $this->temporaryDirectory . '/linked-app';
        $this->createApp($app);

        if (!function_exists('symlink') || !@symlink($app, $link)) {
            $this->markTestSkipped('Symbolic links are not available');
        }

        $result = $this->runConsole($link);

        $this->assertSuccessfulPath($result, $app);
    }

    public function testExistingInvalidDirectoryReportsAcceptedPathForms(): void
    {
        $invalid = $this->temporaryDirectory . '/invalid';
        mkdir($invalid);

        $result = $this->runConsole($invalid);

        $this->assertNotSame(0, $result['status']);
        $this->assertStringContainsString(
            '--path puede apuntar al directorio de la aplicación o a la raíz del proyecto KumbiaPHP',
            $result['stdout'] . $result['stderr']
        );
    }

    public function testNonexistentExplicitPathKeepsInvalidPathError(): void
    {
        $invalid = $this->temporaryDirectory . '/does-not-exist';

        $result = $this->runConsole($invalid);

        $this->assertNotSame(0, $result['status']);
        $this->assertStringContainsString(
            "La ruta \"$invalid\" es invalida",
            $result['stdout'] . $result['stderr']
        );
    }

    public function testModelCreateAcceptsSafeNestedName(): void
    {
        $app = $this->temporaryDirectory . '/model-create-safe-app';
        $this->createApp($app);
        mkdir($app . '/models');

        $result = $this->runConsoleCommand($app, 'model', 'create', ['admin/user']);

        $this->assertSame('', $result['stderr']);
        $this->assertSame(0, $result['status']);
        $this->assertFileExists($app . '/models/admin/user.php');
    }

    #[DataProvider('unsafeModelNameProvider')]
    public function testModelCreateRejectsUnsafeNames(string $model): void
    {
        $app = $this->temporaryDirectory . '/model-create-app';
        $this->createApp($app);
        mkdir($app . '/models');

        $result = $this->runConsoleCommand($app, 'model', 'create', [$model]);

        $this->assertUnsafePathRejected($result);
        $this->assertFileDoesNotExist($app . '/outside.php');
        $this->assertFileDoesNotExist($this->temporaryDirectory . '/outside.php');
    }

    #[DataProvider('unsafeModelNameProvider')]
    public function testModelDeleteRejectsUnsafeNames(string $model): void
    {
        $app = $this->temporaryDirectory . '/model-delete-app';
        $this->createApp($app);
        mkdir($app . '/models');
        file_put_contents($app . '/outside.php', 'outside');

        $result = $this->runConsoleCommand($app, 'model', 'delete', [$model]);

        $this->assertUnsafePathRejected($result);
        $this->assertFileExists($app . '/outside.php');
    }

    public static function unsafeModelNameProvider(): array
    {
        return [
            ['../outside'],
            ['.'],
            ['..'],
            ['/outside'],
            ['nested\\outside'],
        ];
    }

    public function testControllerCreateAcceptsSafeNestedName(): void
    {
        $app = $this->temporaryDirectory . '/controller-create-safe-app';
        $this->createApp($app);
        mkdir($app . '/controllers');
        mkdir($app . '/views');

        $result = $this->runConsoleCommand($app, 'controller', 'create', ['admin/users']);

        $this->assertSame('', $result['stderr']);
        $this->assertSame(0, $result['status']);
        $this->assertFileExists($app . '/controllers/admin/users_controller.php');
        $this->assertDirectoryExists($app . '/views/admin/users');
    }

    #[DataProvider('unsafeControllerNameProvider')]
    public function testControllerCreateRejectsUnsafeNames(string $controller): void
    {
        $app = $this->temporaryDirectory . '/controller-create-app';
        $this->createApp($app);
        mkdir($app . '/controllers');
        mkdir($app . '/views');

        $result = $this->runConsoleCommand($app, 'controller', 'create', [$controller]);

        $this->assertUnsafePathRejected($result);
        $this->assertFileDoesNotExist($app . '/outside_controller.php');
        $this->assertDirectoryDoesNotExist($app . '/views/../outside');
    }

    #[DataProvider('unsafeControllerNameProvider')]
    public function testControllerDeleteRejectsUnsafeNames(string $controller): void
    {
        $app = $this->temporaryDirectory . '/controller-delete-app';
        $this->createApp($app);
        mkdir($app . '/controllers');
        mkdir($app . '/views');
        file_put_contents($app . '/outside_controller.php', 'outside');

        $result = $this->runConsoleCommand($app, 'controller', 'delete', [$controller]);

        $this->assertUnsafePathRejected($result);
        $this->assertFileExists($app . '/outside_controller.php');
    }

    public static function unsafeControllerNameProvider(): array
    {
        return [
            ['../outside'],
            ['.'],
            ['..'],
            ['/outside'],
            ['nested\\outside'],
        ];
    }

    private function createApp(string $app, string $configFile = 'config.php'): void
    {
        mkdir($app . '/config', 0777, true);
        mkdir($app . '/extensions/console', 0777, true);

        $config = $configFile === 'config.php'
            ? "<?php\nreturn ['application' => []];\n"
            : "[application]\n";
        file_put_contents($app . "/config/$configFile", $config);
        file_put_contents(
            $app . '/extensions/console/path_probe_console.php',
            "<?php\nclass PathProbeConsole\n{\n    public function main()\n    {\n        echo APP_PATH;\n    }\n\n    public function production()\n    {\n        echo PRODUCTION;\n    }\n}\n"
        );
    }

    private function runConsole(?string $path, ?string $cwd = null, string $command = 'main'): array
    {
        return $this->runConsoleCommand($path, 'path_probe', $command, [], $cwd);
    }

    private function runConsoleCommand(
        ?string $path,
        string $console,
        string $action,
        array $arguments = [],
        ?string $cwd = null
    ): array {
        $command = [PHP_BINARY, $this->consoleEntrypoint, $console, $action];
        if ($path !== null) {
            $command[] = "--path=$path";
        }
        foreach ($arguments as $argument) {
            $command[] = $argument;
        }

        $process = proc_open(
            $command,
            [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes,
            $cwd ?? $this->temporaryDirectory
        );
        if (!is_resource($process)) {
            $this->fail('Unable to start the console subprocess');
        }

        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        return [
            'status' => proc_close($process),
            'stdout' => $stdout,
            'stderr' => $stderr,
        ];
    }

    private function assertSuccessfulPath(array $result, string $app): void
    {
        $this->assertSame('', $result['stderr']);
        $this->assertSame(0, $result['status']);
        $this->assertSame(realpath($app) . DIRECTORY_SEPARATOR, $result['stdout']);
    }

    private function assertUnsafePathRejected(array $result): void
    {
        $this->assertNotSame(0, $result['status']);
        $this->assertStringContainsString(
            'La ruta indicada no es segura',
            $result['stdout'] . $result['stderr']
        );
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
