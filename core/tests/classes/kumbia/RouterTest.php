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
            RouterController::$stopBeforeFilter = false;
        }
    }

    public function testInitStoresRouteAndRequestMethod(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        Router::init('/router/show/1');

        $this->assertSame('/router/show/1', Router::get('route'));
        $this->assertSame('POST', Router::get('method'));
    }

    public function testInitRejectsParentDirectoryTraversal(): void
    {
        $this->expectException(KumbiaException::class);
        $this->expectExceptionMessage("Posible intento de hack en URL: '/admin/../config'");

        Router::init('/admin/../config');
    }

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

    public function testKumbiaRouterRewriteReturnsNoOverridesForRoot(): void
    {
        $this->assertSame([], KumbiaRouter::rewrite('/'));
    }

    public function testKumbiaRouterRewriteNormalizesControllerActionAndParameters(): void
    {
        $this->assertSame([
            'controller' => 'blog_posts',
            'controller_path' => 'blog_posts',
            'action' => 'show',
            'parameters' => ['10', 'hello-world'],
        ], KumbiaRouter::rewrite('/blog-posts/show/10/hello-world/'));
    }

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

    public function testKumbiaRouterRewriteUsesModuleIndexControllerWhenOnlyModuleIsPresent(): void
    {
        $this->assertSame([
            'module' => 'admin',
            'controller_path' => 'admin/index',
        ], KumbiaRouter::rewrite('/admin'));
    }

    public function testKumbiaRouterIfRoutedReturnsExactRoute(): void
    {
        Config::set('routes.routes./legacy', '/router/show/1');

        $this->assertSame('/router/show/1', KumbiaRouter::ifRouted('/legacy'));
    }

    public function testKumbiaRouterIfRoutedAppliesGlobalWildcard(): void
    {
        Config::set('routes.routes./*', '/router*');

        $this->assertSame('/router/admin/show', KumbiaRouter::ifRouted('/admin/show'));
    }

    public function testKumbiaRouterIfRoutedAppliesPrefixWildcard(): void
    {
        Config::set('routes.routes./blog/*', '/router/show/*');

        $this->assertSame('/router/show/10', KumbiaRouter::ifRouted('/blog/10'));
    }

    public function testKumbiaRouterIfRoutedReturnsOriginalUrlWhenNoRouteMatches(): void
    {
        Config::set('routes.routes./blog/*', '/router/show/*');

        $this->assertSame('/pages/about', KumbiaRouter::ifRouted('/pages/about'));
    }

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

    public function testKumbiaRouterGetControllerRejectsMissingController(): void
    {
        $this->expectException(KumbiaException::class);

        set_error_handler(static fn () => true);

        try {
            KumbiaRouter::getController([
                'module' => '',
                'controller' => 'missing',
                'action' => 'index',
                'parameters' => [],
                'controller_path' => 'missing',
            ]);
        } finally {
            restore_error_handler();
        }
    }

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

    public function testExecuteUsesLegacyConfiguredRoutes(): void
    {
        Config::set('config.application.routes', '1');
        Config::set('routes.routes./legacy', '/router/target/routed');

        Router::execute('/legacy');

        $this->assertSame('target', Router::get('action'));
        $this->assertSame(['routed'], Router::get('parameters'));
        $this->assertContains('target:routed', RouterController::$events);
    }

    public function testExecuteUsesConfiguredRouterClass(): void
    {
        Config::set('config.application.routes', RouterTestCustomRouter::class);

        Router::execute('/anything');

        $this->assertSame('target', Router::get('action'));
        $this->assertSame(['custom'], Router::get('parameters'));
        $this->assertContains('target:custom', RouterController::$events);
    }

    public function testDispatchStopsWhenInitialCallbacksReturnFalse(): void
    {
        require_once APP_PATH.'controllers/router_controller.php';
        RouterController::$stopBeforeFilter = true;
        $controller = new RouterController([
            'module' => '',
            'controller' => 'router',
            'action' => 'index',
            'parameters' => [],
            'controller_path' => 'router',
        ]);

        $this->dispatch($controller);

        $this->assertSame(['initialize', 'before_filter'], RouterController::$events);
    }

    public function testDispatchRejectsReservedCallbackAction(): void
    {
        $this->expectException(KumbiaException::class);
        $this->expectExceptionMessage('Esta intentando ejecutar un método reservado de KumbiaPHP');

        require_once APP_PATH.'controllers/router_controller.php';
        $controller = new RouterController([
            'module' => '',
            'controller' => 'router',
            'action' => 'k_callback',
            'parameters' => [],
            'controller_path' => 'router',
        ]);

        $this->dispatch($controller);
    }

    public function testDispatchRejectsMissingAction(): void
    {
        $this->expectException(KumbiaException::class);

        require_once APP_PATH.'controllers/router_controller.php';
        $controller = new RouterController([
            'module' => '',
            'controller' => 'router',
            'action' => 'missing',
            'parameters' => [],
            'controller_path' => 'router',
        ]);

        $this->dispatch($controller);
    }

    public function testDispatchRejectsInvalidActionParameterCount(): void
    {
        $this->expectException(KumbiaException::class);

        require_once APP_PATH.'controllers/router_controller.php';
        $controller = new RouterController([
            'module' => '',
            'controller' => 'router',
            'action' => 'show',
            'parameters' => [],
            'controller_path' => 'router',
        ]);

        $this->dispatch($controller);
    }

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
