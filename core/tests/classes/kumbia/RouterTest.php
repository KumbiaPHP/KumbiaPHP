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

use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

require_once CORE_PATH.'kumbia/kumbia_view.php';

if (!class_exists('View', false)) {
    class View extends KumbiaView
    {
    }
}

class RouterTestCustomRouter
{
    public static function rewrite(string $url): array
    {
        return [
            'route' => $url,
            'method' => $_SERVER['REQUEST_METHOD'],
            'controller' => 'router',
            'action' => 'target',
            'parameters' => ['custom'],
            'controller_path' => 'router',
        ];
    }

    public static function getController(array $params)
    {
        require_once APP_PATH.'controllers/router_controller.php';

        return new RouterController($params);
    }
}

/**
 * @category    Test
 * @package     Core
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
#[PreserveGlobalState(false)]
class RouterTest extends TestCase
{
    private array $originalServer;
    private array $originalRouterVars;
    private string $originalRouterClass;
    private bool $originalRouted;
    private array $originalConfig;

    protected function setUp(): void
    {
        require_once CORE_PATH.'kumbia/config.php';
        require_once CORE_PATH.'kumbia/kumbia_view.php';
        require_once CORE_PATH.'kumbia/controller.php';
        require_once CORE_PATH.'kumbia/router.php';

        $this->originalServer = $_SERVER;
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->originalRouterVars = $this->getStaticProperty(Router::class, 'vars');
        $this->originalRouterClass = $this->getStaticProperty(Router::class, 'router');
        $this->originalRouted = $this->getStaticProperty(Router::class, 'routed');
        $this->originalConfig = $this->getStaticProperty(Config::class, 'config');

        $this->setStaticProperty(Router::class, 'vars', []);
        $this->setStaticProperty(Router::class, 'router', 'KumbiaRouter');
        $this->setStaticProperty(Router::class, 'routed', false);
        $this->setStaticProperty(Config::class, 'config', []);

        if (class_exists('RouterController', false)) {
            RouterController::$events = [];
            RouterController::$stopInitialize = false;
            RouterController::$stopBeforeFilter = false;
        }
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->originalServer;
        $this->setStaticProperty(Router::class, 'vars', $this->originalRouterVars);
        $this->setStaticProperty(Router::class, 'router', $this->originalRouterClass);
        $this->setStaticProperty(Router::class, 'routed', $this->originalRouted);
        $this->setStaticProperty(Config::class, 'config', $this->originalConfig);

        if (class_exists('RouterController', false)) {
            RouterController::$events = [];
            RouterController::$stopInitialize = false;
            RouterController::$stopBeforeFilter = false;
        }
    }

    // Phase 1: Router initialization and state access

    #[RunInSeparateProcess]
    public function testInitStoresRouteAndRequestMethod(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        Router::init('/router/show/1');

        $this->assertSame('/router/show/1', Router::get('route'));
        $this->assertSame('POST', Router::get('method'));
    }

    #[RunInSeparateProcess]
    public function testInitRejectsParentDirectoryTraversal(): void
    {
        $this->expectException(KumbiaException::class);
        $this->expectExceptionMessage("Posible intento de hack en URL: '/admin/../config'");

        Router::init('/admin/../config');
    }

    #[RunInSeparateProcess]
    public function testGetReturnsAllVarsOrOneValue(): void
    {
        Router::to([
            'module' => 'admin',
            'controller' => 'router',
            'action' => 'show',
            'parameters' => ['10'],
            'controller_path' => 'admin/router',
        ]);

        $this->assertSame('router', Router::get('controller'));
        $this->assertSame([
            'module' => 'admin',
            'controller' => 'router',
            'action' => 'show',
            'parameters' => ['10'],
            'controller_path' => 'admin/router',
        ], Router::get());
    }

    #[RunInSeparateProcess]
    public function testToMergesDefaults(): void
    {
        Router::to(['controller' => 'posts']);

        $this->assertSame([
            'controller' => 'posts',
            'module' => '',
            'action' => 'index',
            'parameters' => [],
            'controller_path' => 'index',
        ], Router::get());
    }

    // Phase 2: URL rewriting

    #[RunInSeparateProcess]
    public function testKumbiaRouterRewriteReturnsNoOverridesForRoot(): void
    {
        $this->assertSame([], KumbiaRouter::rewrite('/'));
    }

    #[RunInSeparateProcess]
    public function testKumbiaRouterRewriteNormalizesControllerActionAndParameters(): void
    {
        $this->assertSame([
            'controller' => 'blog_posts',
            'controller_path' => 'blog_posts',
            'action' => 'show',
            'parameters' => ['10', 'hello-world'],
        ], KumbiaRouter::rewrite('/blog-posts/show/10/hello-world/'));
    }

    #[RunInSeparateProcess]
    public function testKumbiaRouterRewriteDetectsModuleAndControllerPath(): void
    {
        $this->assertSame([
            'module' => 'admin',
            'controller' => 'router',
            'controller_path' => 'admin/router',
            'action' => 'show',
            'parameters' => ['42'],
        ], KumbiaRouter::rewrite('/admin/router/show/42'));
    }

    #[RunInSeparateProcess]
    public function testKumbiaRouterRewriteUsesModuleIndexControllerWhenOnlyModuleIsPresent(): void
    {
        $this->assertSame([
            'module' => 'admin',
            'controller_path' => 'admin/index',
        ], KumbiaRouter::rewrite('/admin'));
    }

    #[RunInSeparateProcess]
    public function testKumbiaRouterRewriteReturnsControllerWhenNoActionIsPresent(): void
    {
        $this->assertSame([
            'controller' => 'router',
            'controller_path' => 'router',
        ], KumbiaRouter::rewrite('/router'));
    }

    #[RunInSeparateProcess]
    public function testKumbiaRouterRewriteReturnsActionWithoutParameters(): void
    {
        $this->assertSame([
            'controller' => 'router',
            'controller_path' => 'router',
            'action' => 'show',
        ], KumbiaRouter::rewrite('/router/show'));
    }

    // Phase 3: Configured route matching

    #[RunInSeparateProcess]
    public function testKumbiaRouterIfRoutedReturnsExactRoute(): void
    {
        Config::set('routes.routes./legacy', '/router/show/1');

        $this->assertSame('/router/show/1', KumbiaRouter::ifRouted('/legacy'));
    }

    #[RunInSeparateProcess]
    public function testKumbiaRouterIfRoutedAppliesGlobalWildcard(): void
    {
        Config::set('routes.routes./*', '/router*');

        $this->assertSame('/router/admin/show', KumbiaRouter::ifRouted('/admin/show'));
    }

    #[RunInSeparateProcess]
    public function testKumbiaRouterIfRoutedAppliesPrefixWildcard(): void
    {
        Config::set('routes.routes./blog/*', '/router/show/*');

        $this->assertSame('/router/show/10', KumbiaRouter::ifRouted('/blog/10'));
    }

    #[RunInSeparateProcess]
    public function testKumbiaRouterIfRoutedReturnsOriginalUrlWhenNoRouteMatches(): void
    {
        Config::set('routes.routes./blog/*', '/router/show/*');

        $this->assertSame('/pages/about', KumbiaRouter::ifRouted('/pages/about'));
    }

    #[RunInSeparateProcess]
    public function testKumbiaRouterIfRoutedPrefersExactRouteOverWildcard(): void
    {
        Config::set('routes.routes./*', '/router/show/*');
        Config::set('routes.routes./blog/10', '/router/target/exact');

        $this->assertSame('/router/target/exact', KumbiaRouter::ifRouted('/blog/10'));
    }

    // Phase 4: Controller resolution

    #[RunInSeparateProcess]
    public function testKumbiaRouterGetControllerBuildsControllerInstance(): void
    {
        $controller = KumbiaRouter::getController([
            'module' => '',
            'controller' => 'router',
            'action' => 'index',
            'parameters' => [],
            'controller_path' => 'router',
        ]);

        $this->assertInstanceOf(RouterController::class, $controller);
        $this->assertSame('router', $controller->controller_name);
        $this->assertSame('index', $controller->action_name);
    }

    #[RunInSeparateProcess]
    public function testKumbiaRouterGetControllerRejectsMissingController(): void
    {
        $exception = null;
        set_error_handler(static function (int $severity, string $message): bool {
            return $severity === E_WARNING && str_contains($message, 'missing_controller.php');
        });

        try {
            KumbiaRouter::getController($this->routerParameters('index', [], 'missing'));
        } catch (KumbiaException $caught) {
            $exception = $caught;
        } finally {
            restore_error_handler();
        }

        $this->assertInstanceOf(KumbiaException::class, $exception);
        $this->assertSame(0, $exception->getCode());
        $reflection = new ReflectionProperty(KumbiaException::class, 'view');
        $reflection->setAccessible(true);
        $this->assertSame('no_controller', $reflection->getValue($exception));
    }

    // Phase 5: Public route execution

    #[RunInSeparateProcess]
    public function testExecuteDispatchesDefaultRouterAction(): void
    {
        Config::set('config.application.routes', null);

        $controller = Router::execute('/router/show/7/custom-slug');

        $this->assertInstanceOf(RouterController::class, $controller);
        $this->assertSame([
            'initialize',
            'before_filter',
            'show:7:custom-slug',
            'after_filter',
            'finalize',
        ], RouterController::$events);
    }

    #[RunInSeparateProcess]
    public function testExecutePreservesRouteAndRequestMethod(): void
    {
        Config::set('config.application.routes', null);
        $_SERVER['REQUEST_METHOD'] = 'POST';

        Router::execute('/router/show/7/custom-slug');

        $this->assertSame('/router/show/7/custom-slug', Router::get('route'));
        $this->assertSame('POST', Router::get('method'));
    }

    #[RunInSeparateProcess]
    public function testExecuteUsesLegacyConfiguredRoutes(): void
    {
        Config::set('config.application.routes', '1');
        Config::set('routes.routes./legacy', '/router/target/routed');

        Router::execute('/legacy');

        $this->assertRouterState([
            'route' => '/legacy',
            'method' => 'GET',
            'module' => '',
            'controller' => 'router',
            'action' => 'target',
            'parameters' => ['routed'],
            'controller_path' => 'router',
        ]);
        $this->assertContains('target:routed', RouterController::$events);
    }

    #[RunInSeparateProcess]
    public function testExecuteUsesConfiguredRouterClass(): void
    {
        Config::set('config.application.routes', RouterTestCustomRouter::class);

        Router::execute('/anything');

        $this->assertRouterState([
            'route' => '/anything',
            'method' => 'GET',
            'module' => '',
            'controller' => 'router',
            'action' => 'target',
            'parameters' => ['custom'],
            'controller_path' => 'router',
        ]);
        $this->assertContains('target:custom', RouterController::$events);
    }

    // Phase 6: Dispatch lifecycle and validation

    #[RunInSeparateProcess]
    public function testDispatchStopsWhenInitializeReturnsFalse(): void
    {
        require_once APP_PATH.'controllers/router_controller.php';
        RouterController::$stopInitialize = true;
        $controller = new RouterController($this->routerParameters());

        $this->dispatch($controller);

        $this->assertSame(['initialize'], RouterController::$events);
    }

    #[RunInSeparateProcess]
    public function testDispatchStopsWhenBeforeFilterReturnsFalse(): void
    {
        require_once APP_PATH.'controllers/router_controller.php';
        RouterController::$stopBeforeFilter = true;
        $controller = new RouterController($this->routerParameters());

        $this->dispatch($controller);

        $this->assertSame(['initialize', 'before_filter'], RouterController::$events);
    }

    #[RunInSeparateProcess]
    public function testDispatchRejectsReservedCallbackAction(): void
    {
        $this->expectException(KumbiaException::class);
        $this->expectExceptionMessage('Esta intentando ejecutar un método reservado de KumbiaPHP');

        require_once APP_PATH.'controllers/router_controller.php';
        $controller = new RouterController($this->routerParameters('k_callback'));

        $this->dispatch($controller);
    }

    #[RunInSeparateProcess]
    public function testDispatchRejectsMissingAction(): void
    {
        require_once APP_PATH.'controllers/router_controller.php';
        $controller = new RouterController($this->routerParameters('missing'));
        $exception = null;

        try {
            $this->dispatch($controller);
        } catch (KumbiaException $caught) {
            $exception = $caught;
        }

        $this->assertInstanceOf(KumbiaException::class, $exception);
        $this->assertNotContains('after_filter', RouterController::$events);
        $this->assertNotContains('finalize', RouterController::$events);
    }

    #[RunInSeparateProcess]
    public function testDispatchRejectsInvalidActionParameterCount(): void
    {
        $this->expectException(KumbiaException::class);

        require_once APP_PATH.'controllers/router_controller.php';
        $controller = new RouterController($this->routerParameters('show'));

        $this->dispatch($controller);
    }

    #[RunInSeparateProcess]
    public function testDispatchRejectsTooManyActionParameters(): void
    {
        $this->expectException(KumbiaException::class);

        require_once APP_PATH.'controllers/router_controller.php';
        $controller = new RouterController([
            'module' => '',
            'controller' => 'router',
            'action' => 'show',
            'parameters' => ['1', 'slug', 'extra'],
            'controller_path' => 'router',
        ]);

        $this->dispatch($controller);
    }

    #[RunInSeparateProcess]
    public function testDispatchAllowsExtraParametersWhenLimitParamsIsDisabled(): void
    {
        require_once APP_PATH.'controllers/router_controller.php';
        $controller = new RouterController([
            'module' => '',
            'controller' => 'router',
            'action' => 'show',
            'parameters' => ['1', 'slug', 'extra'],
            'controller_path' => 'router',
        ]);
        $controller->limit_params = false;

        $this->dispatch($controller);

        $this->assertContains('show:1:slug', RouterController::$events);
    }

    // Phase 7: Internal redirects

    #[RunInSeparateProcess]
    public function testDispatchRunsInternalRedirectOnce(): void
    {
        Config::set('config.application.routes', null);

        Router::execute('/router/redirect');

        $this->assertSame([
            'initialize',
            'before_filter',
            'redirect',
            'after_filter',
            'finalize',
            'initialize',
            'before_filter',
            'target:internal',
            'after_filter',
            'finalize',
        ], RouterController::$events);
        $this->assertFalse($this->getStaticProperty(Router::class, 'routed'));
    }

    private function routerParameters(string $action = 'index', array $parameters = [], string $controllerPath = 'router'): array
    {
        return [
            'module' => '',
            'controller' => 'router',
            'action' => $action,
            'parameters' => $parameters,
            'controller_path' => $controllerPath,
        ];
    }

    private function assertRouterState(array $expected): void
    {
        foreach ($expected as $field => $value) {
            $this->assertSame($value, Router::get($field));
        }
    }

    private function dispatch(Controller $controller): Controller
    {
        $method = new ReflectionMethod(Router::class, 'dispatch');
        $method->setAccessible(true);

        return $method->invoke(null, $controller);
    }

    private function getStaticProperty(string $class, string $property): mixed
    {
        $reflection = new ReflectionProperty($class, $property);
        $reflection->setAccessible(true);

        return $reflection->getValue();
    }

    private function setStaticProperty(string $class, string $property, mixed $value): void
    {
        $reflection = new ReflectionProperty($class, $property);
        $reflection->setAccessible(true);
        $reflection->setValue(null, $value);
    }
}
