<?php

use PHPUnit\Framework\TestCase;

if (!class_exists(KumbiaView::class, false)) {
    require_once CORE_PATH.'kumbia/kumbia_view.php';
}
if (!class_exists(View::class, false)) {
    class_alias(KumbiaView::class, View::class);
}
if (!class_exists(Controller::class, false)) {
    require_once CORE_PATH.'kumbia/controller.php';
}

abstract class RouterTestCase extends TestCase
{
    private array $staticState = [];
    private bool $requestMethodWasSet;
    private mixed $requestMethod;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadRouterDependencies();

        $this->requestMethodWasSet = array_key_exists('REQUEST_METHOD', $_SERVER);
        $this->requestMethod = $_SERVER['REQUEST_METHOD'] ?? null;

        $this->staticState = [
            Config::class => $this->snapshotStaticProperties(Config::class),
            Router::class => $this->snapshotStaticProperties(Router::class),
            StaticRouter::class => $this->snapshotStaticProperties(StaticRouter::class),
            KumbiaView::class => $this->snapshotStaticProperties(KumbiaView::class),
        ];

        Config::set('config.application.routes', false);
        Config::set('routes.routes', []);
        $_SERVER['REQUEST_METHOD'] = 'GET';
        RouterScenarioRouter::reset();
        RouterEventLog::reset();
        $this->setStaticProperty(Router::class, 'vars', []);
        $this->setStaticProperty(Router::class, 'default', [
            'module' => '',
            'controller' => 'index',
            'action' => 'index',
            'parameters' => [],
            'controller_path' => 'index',
        ]);
        $this->setStaticProperty(Router::class, 'router', KumbiaRouter::class);
        $this->setStaticProperty(Router::class, 'routed', false);
        $this->setStaticProperty(StaticRouter::class, 'routes', []);
    }

    protected function tearDown(): void
    {
        foreach ($this->staticState as $class => $properties) {
            foreach ($properties as $name => $value) {
                $this->setStaticProperty($class, $name, $value);
            }
        }

        if ($this->requestMethodWasSet) {
            $_SERVER['REQUEST_METHOD'] = $this->requestMethod;
        } else {
            unset($_SERVER['REQUEST_METHOD']);
        }

        foreach (array_keys($GLOBALS) as $name) {
            if (str_starts_with($name, 'router_probe_')) {
                unset($GLOBALS[$name]);
            }
        }

        parent::tearDown();
    }

    protected function useScenarioRouter(): void
    {
        $this->setStaticProperty(Router::class, 'router', RouterScenarioRouter::class);
    }

    protected function setStaticProperty(string $class, string $property, mixed $value): void
    {
        $reflection = new ReflectionProperty($class, $property);
        $reflection->setAccessible(true);
        $reflection->setValue(null, $value);
    }

    protected function assertRouterState(array $expected): void
    {
        $this->assertSame(
            ['module', 'controller', 'action', 'parameters', 'controller_path', 'route', 'method'],
            array_keys($expected)
        );
        $actual = Router::get();
        $this->assertSame([], array_diff_key($actual, $expected));
        $this->assertSame([], array_diff_key($expected, $actual));
        foreach ($expected as $key => $value) {
            $this->assertSame($value, Router::get($key), "Unexpected Router::get('$key') value");
        }
    }

    protected function assertControllerRoute(Controller $controller, array $expected): void
    {
        $this->assertSame($expected['module'], $controller->module_name);
        $this->assertSame($expected['controller'], $controller->controller_name);
        $this->assertSame($expected['action'], $controller->action_name);
        $this->assertSame($expected['parameters'], $controller->parameters);
    }

    protected function expectMissingController(string $controllerPath, callable $operation): KumbiaException
    {
        $expectedFile = APP_PATH."controllers/{$controllerPath}_controller.php";
        $warnings = [];
        set_error_handler(
            static function (int $severity, string $message) use ($expectedFile, &$warnings): bool {
                if ($severity === E_WARNING && str_contains($message, $expectedFile)) {
                    $warnings[] = $message;
                    return true;
                }
                return false;
            }
        );

        try {
            $operation();
            $this->fail('Expected a missing-controller exception.');
        } catch (KumbiaException $exception) {
            $this->assertNotEmpty($warnings, 'The expected include warning was not observed.');
            return $exception;
        } finally {
            restore_error_handler();
        }
    }

    private function loadRouterDependencies(): void
    {
        if (!class_exists(Config::class, false)) {
            require_once CORE_PATH.'kumbia/config.php';
        }
        if (!class_exists(KumbiaView::class, false)) {
            require_once CORE_PATH.'kumbia/kumbia_view.php';
        }
        if (!class_exists(View::class, false)) {
            class_alias(KumbiaView::class, View::class);
        }
        if (!class_exists(Controller::class, false)) {
            require_once CORE_PATH.'kumbia/controller.php';
        }
        if (!class_exists(KumbiaRouter::class, false)) {
            require_once CORE_PATH.'kumbia/kumbia_router.php';
        }
        if (!class_exists(Router::class, false)) {
            require_once CORE_PATH.'kumbia/router.php';
        }
        if (!class_exists(StaticRouter::class, false)) {
            require_once CORE_PATH.'kumbia/static_router.php';
        }
    }

    private function snapshotStaticProperties(string $class): array
    {
        $values = [];
        $reflection = new ReflectionClass($class);
        foreach ($reflection->getProperties(ReflectionProperty::IS_STATIC) as $property) {
            $property->setAccessible(true);
            $values[$property->getName()] = $property->getValue();
        }
        return $values;
    }
}

final class RouterEventLog
{
    public static array $events = [];

    public static function reset(): void
    {
        self::$events = [];
    }
}

class RouterScenarioRouter
{
    public static array $rewrites = [];
    public static array $routedUrls = [];
    public static array $controllerVariables = [];
    public static array $routes = [];
    public static array $aliases = [];
    public static $controllerFactory;

    public static function reset(): void
    {
        self::$rewrites = [];
        self::$routedUrls = [];
        self::$controllerVariables = [];
        self::$routes = [];
        self::$aliases = [];
        self::$controllerFactory = null;
    }

    public static function rewrite(string $url): array
    {
        self::$rewrites[] = $url;
        return self::$routes[$url] ?? [];
    }

    public static function ifRouted(string $url): string
    {
        self::$routedUrls[] = $url;
        return self::$aliases[$url] ?? $url;
    }

    public static function getController(array $variables): Controller
    {
        self::$controllerVariables[] = $variables;
        $factory = self::$controllerFactory;
        return $factory($variables);
    }
}

class RouterRecordingController extends Controller
{
    public array $events = [];
    public mixed $initializeResult = null;
    public mixed $beforeResult = null;
    public int $actionCalls = 0;
    public array $received = [];

    protected function initialize()
    {
        $this->events[] = 'initialize';
        RouterEventLog::$events[] = $this->controller_name.':initialize';
        return $this->initializeResult;
    }

    protected function before_filter()
    {
        $this->events[] = 'before_filter';
        RouterEventLog::$events[] = $this->controller_name.':before_filter';
        return $this->beforeResult;
    }

    public function index(): void
    {
        $this->recordAction('index', []);
    }

    public function show(string $required, ?string $optional = null): void
    {
        $arguments = func_num_args() === 1 ? [$required] : [$required, $optional];
        $this->recordAction('show', $arguments);
    }

    public function redirect(): void
    {
        $this->recordAction('redirect', []);
        Router::to([
            'module' => '',
            'controller' => 'destination',
            'action' => 'show',
            'parameters' => ['42'],
            'controller_path' => 'destination',
        ], true);
    }

    protected function after_filter()
    {
        $this->events[] = 'after_filter';
        RouterEventLog::$events[] = $this->controller_name.':after_filter';
    }

    protected function finalize()
    {
        $this->events[] = 'finalize';
        RouterEventLog::$events[] = $this->controller_name.':finalize';
    }

    private function recordAction(string $name, array $arguments): void
    {
        ++$this->actionCalls;
        $this->received = $arguments;
        $this->events[] = $name;
        RouterEventLog::$events[] = $this->controller_name.':'.$name;
    }
}
