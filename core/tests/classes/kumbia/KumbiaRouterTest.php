<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

require_once dirname(__DIR__, 2).'/support/RouterTestCase.php';

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class KumbiaRouterTest extends RouterTestCase
{
    /** Verifies the root route rewrites and executes with default route values. */
    public function testA1RootRewriteAndExecutionApplyDefaults(): void
    {
        $this->assertSame([], KumbiaRouter::rewrite('/'));

        $this->useScenarioRouter();
        RouterScenarioRouter::$controllerFactory = static fn (array $variables): Controller => new RouterRecordingController($variables);

        $controller = Router::execute('/');
        $expected = [
            'module' => '',
            'controller' => 'index',
            'action' => 'index',
            'parameters' => [],
            'controller_path' => 'index',
            'route' => '/',
            'method' => 'GET',
        ];

        $this->assertControllerRoute($controller, $expected);
        $this->assertRouterState($expected);
        $this->assertSame(['/'], RouterScenarioRouter::$rewrites);
    }

    public static function basicRewriteProvider(): array
    {
        return [
            'controller only' => ['/posts', [
                'controller' => 'posts',
                'controller_path' => 'posts',
            ]],
            'action and ordered string parameters' => ['/posts/show/7/blue', [
                'controller' => 'posts',
                'controller_path' => 'posts',
                'action' => 'show',
                'parameters' => ['7', 'blue'],
            ]],
            'hyphenated controller' => ['/my-post/edit/7', [
                'controller' => 'my_post',
                'controller_path' => 'my_post',
                'action' => 'edit',
                'parameters' => ['7'],
            ]],
        ];
    }

    /** Verifies rewrites preserve controller, action, and ordered parameter parts. */
    #[DataProvider('basicRewriteProvider')]
    public function testA2RewritePreservesRouteParts(string $url, array $expected): void
    {
        $this->assertSame($expected, KumbiaRouter::rewrite($url));
    }

    /** Verifies rewrites detect modules and build their controller paths. */
    public function testA3RewriteDetectsModulesAndBuildsControllerPaths(): void
    {
        $this->assertSame([
            'module' => 'router_module',
            'controller_path' => 'router_module/index',
        ], KumbiaRouter::rewrite('/router_module'));

        $this->assertSame([
            'module' => 'router_module',
            'controller' => 'router_compound_name',
            'controller_path' => 'router_module/router_compound_name',
            'action' => 'show',
            'parameters' => ['9'],
        ], KumbiaRouter::rewrite('/router_module/router_compound_name/show/9'));
    }

    /** Verifies exact routes take precedence while unmatched routes remain unchanged. */
    public function testA5ExactRoutesWinAndMissingRoutesRemainUnchanged(): void
    {
        Config::set('routes.routes', []);
        $this->assertSame('/unmatched', KumbiaRouter::ifRouted('/unmatched'));

        Config::set('routes.routes', [
            '/about' => '/pages/show/about',
            '/*' => '/index*',
        ]);
        $this->assertSame('/pages/show/about', KumbiaRouter::ifRouted('/about'));
    }

    /** Verifies wildcard routes substitute the captured suffix into their target. */
    public function testA6WildcardRoutesSubstituteTheirCapturedSuffix(): void
    {
        Config::set('routes.routes', ['/post/*' => '/posts/*']);
        $this->assertSame('/posts/7', KumbiaRouter::ifRouted('/post/7'));

        Config::set('routes.routes', ['/*' => '/index*']);
        $this->assertSame('/index/unknown/path', KumbiaRouter::ifRouted('/unknown/path'));
    }

    /** Verifies the loader instantiates compound and module controller classes. */
    public function testD1ControllerLoaderInstantiatesCompoundAndModuleControllers(): void
    {
        $compoundVariables = [
            'module' => 'router_module',
            'controller' => 'router_compound_name',
            'action' => 'show',
            'parameters' => ['9'],
            'controller_path' => 'router_module/router_compound_name',
            'route' => '/router_module/router_compound_name/show/9',
            'method' => 'GET',
        ];
        $compound = KumbiaRouter::getController($compoundVariables);

        $this->assertInstanceOf(RouterCompoundNameController::class, $compound);
        $this->assertControllerRoute($compound, $compoundVariables);

        $indexVariables = [
            'module' => 'router_module',
            'controller' => 'index',
            'action' => 'index',
            'parameters' => [],
            'controller_path' => 'router_module/index',
            'route' => '/router_module',
            'method' => 'GET',
        ];
        $index = KumbiaRouter::getController($indexVariables);

        $this->assertInstanceOf(IndexController::class, $index);
        $this->assertControllerRoute($index, $indexVariables);
    }

    /** Verifies a missing controller raises a controlled exception without defining its class. */
    public function testD2MissingControllerRaisesControlledException(): void
    {
        $exception = $this->expectMissingController(
            'router_missing',
            static fn () => KumbiaRouter::getController([
                'module' => '',
                'controller' => 'router_missing',
                'action' => 'index',
                'parameters' => [],
                'controller_path' => 'router_missing',
            ])
        );

        $this->assertInstanceOf(KumbiaException::class, $exception);
        $this->assertFalse(class_exists('RouterMissingController', false));
    }

    /** Verifies controller lookup neither includes nor executes application decoy files. */
    public function testE1ApplicationDecoysAreNeitherIncludedNorExecuted(): void
    {
        $positive = Router::execute('/router_posts/show/7');
        $positiveFile = realpath(APP_PATH.'controllers/router_posts_controller.php');
        $this->assertInstanceOf(RouterPostsController::class, $positive);
        $this->assertContains($positiveFile, get_included_files());

        $decoys = [
            ['/config/router_probe', APP_PATH.'config/router_probe.php', 'router_probe_config_executed'],
            ['/models/router_probe', APP_PATH.'models/router_probe.php', 'router_probe_model_executed'],
            ['/libs/router_probe', APP_PATH.'libs/router_probe.php', 'router_probe_lib_executed'],
        ];

        foreach ($decoys as [$url, $file, $marker]) {
            $before = get_included_files();
            $controllerPath = KumbiaRouter::rewrite($url)['controller_path'];
            $this->expectMissingController($controllerPath, static fn () => Router::execute($url));
            $after = get_included_files();

            $this->assertNotContains(realpath($file), $before);
            $this->assertNotContains(realpath($file), $after);
            $this->assertArrayNotHasKey($marker, $GLOBALS);
        }
    }

    /** Verifies a plain PHP decoy in the controller directory is not accepted. */
    public function testE2ControllerDirectoryPhpDecoyIsNotAcceptedAsAController(): void
    {
        $decoy = realpath(APP_PATH.'controllers/router_probe.php');
        foreach (['/router_probe', '/router_probe.php'] as $url) {
            $before = get_included_files();
            $controllerPath = KumbiaRouter::rewrite($url)['controller_path'];
            $this->expectMissingController($controllerPath, static fn () => Router::execute($url));
            $after = get_included_files();

            $this->assertNotContains($decoy, $before);
            $this->assertNotContains($decoy, $after);
            $this->assertArrayNotHasKey('router_probe_controller_file_executed', $GLOBALS);
        }
    }

    /** Verifies traversal is rejected before an external controller decoy executes. */
    public function testE3TraversalIsRejectedBeforeExternalControllerDecoyExecution(): void
    {
        $decoy = realpath(APP_PATH.'models/router_probe_controller.php');
        foreach (['/../models/router_probe', '/admin/../../models/router_probe'] as $url) {
            $this->assertNotContains($decoy, get_included_files());
            try {
                Router::execute($url);
                $this->fail('Expected traversal to be rejected.');
            } catch (KumbiaException) {
                $this->assertNotContains($decoy, get_included_files());
            }
            $this->assertArrayNotHasKey('router_probe_model_controller_executed', $GLOBALS);
        }
    }
}
