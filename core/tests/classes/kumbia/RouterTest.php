<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

require_once dirname(__DIR__, 2).'/support/RouterTestCase.php';

class AlternativeRouterForTest extends RouterScenarioRouter
{
}

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class RouterTest extends RouterTestCase
{
    /** Verifies execution returns the real controller and exposes complete route state. */
    public function testB1ExecuteReturnsRealControllerAndCompletePublicState(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $controller = Router::execute('/router_posts/show/7');
        $expected = [
            'module' => '',
            'controller' => 'router_posts',
            'action' => 'show',
            'parameters' => ['7'],
            'controller_path' => 'router_posts',
            'route' => '/router_posts/show/7',
            'method' => 'POST',
        ];

        $this->assertInstanceOf(RouterPostsController::class, $controller);
        $this->assertControllerRoute($controller, $expected);
        $this->assertSame(1, $controller->showCalls);
        $this->assertSame(['7'], $controller->receivedParameters);
        $this->assertRouterState($expected);
    }

    /** Verifies disabled routing bypasses aliases while preserving the original route. */
    public function testB2DisabledRoutingConfigurationDoesNotConsultAliases(): void
    {
        $this->useScenarioRouter();
        Config::set('config.application.routes', false);
        RouterScenarioRouter::$routes['/original'] = ['controller' => 'plain'];
        RouterScenarioRouter::$controllerFactory = static fn (array $variables): Controller => new RouterRecordingController($variables);

        $controller = Router::execute('/original');

        $this->assertSame([], RouterScenarioRouter::$routedUrls);
        $this->assertSame(['/original'], RouterScenarioRouter::$rewrites);
        $this->assertSame('plain', $controller->controller_name);
        $this->assertSame('/original', Router::get('route'));
    }

    /** Verifies legacy routing applies aliases before rewrites and preserves the original route. */
    public function testB2LegacyRoutingRunsAliasBeforeRewriteAndKeepsOriginalRoute(): void
    {
        $this->useScenarioRouter();
        Config::set('config.application.routes', '1');
        RouterScenarioRouter::$aliases['/original'] = '/aliased';
        RouterScenarioRouter::$routes['/aliased'] = ['controller' => 'alias_target'];
        RouterScenarioRouter::$controllerFactory = static fn (array $variables): Controller => new RouterRecordingController($variables);

        $controller = Router::execute('/original');

        $this->assertSame(['/original'], RouterScenarioRouter::$routedUrls);
        $this->assertSame(['/aliased'], RouterScenarioRouter::$rewrites);
        $this->assertSame('alias_target', $controller->controller_name);
        $this->assertSame('/original', Router::get('route'));
    }

    /** Verifies a configured alternative router resolves and constructs the controller. */
    public function testB2AlternativeRouterOwnsResolutionAndConstruction(): void
    {
        Config::set('config.application.routes', AlternativeRouterForTest::class);
        AlternativeRouterForTest::$routes['/alternative'] = ['controller' => 'alternative'];
        AlternativeRouterForTest::$controllerFactory = static fn (array $variables): Controller => new RouterRecordingController($variables);

        $controller = Router::execute('/alternative');

        $this->assertSame(['/alternative'], AlternativeRouterForTest::$rewrites);
        $this->assertCount(1, AlternativeRouterForTest::$controllerVariables);
        $this->assertSame('alternative', $controller->controller_name);
        $this->assertSame('/alternative', Router::get('route'));
    }

    /** Verifies traversal input is rejected before resolution or callbacks run. */
    public function testB3TraversalIsRejectedBeforeResolutionOrCallbacks(): void
    {
        $this->useScenarioRouter();
        RouterScenarioRouter::$controllerFactory = static fn (array $variables): Controller => new RouterRecordingController($variables);

        try {
            Router::execute('/posts/../secret');
            $this->fail('Expected traversal to be rejected.');
        } catch (KumbiaException) {
            $this->assertSame([], RouterScenarioRouter::$rewrites);
            $this->assertSame([], RouterScenarioRouter::$controllerVariables);
            $this->assertSame([], RouterEventLog::$events);
        }
    }

    /** Verifies dispatch invokes callbacks and the action once in lifecycle order. */
    public function testB4DispatchRunsCallbacksAndActionOnceInOrder(): void
    {
        $this->useScenarioRouter();
        RouterScenarioRouter::$routes['/posts/show/7/blue'] = [
            'controller' => 'posts',
            'action' => 'show',
            'parameters' => ['7', 'blue'],
            'controller_path' => 'posts',
        ];
        RouterScenarioRouter::$controllerFactory = static fn (array $variables): Controller => new RouterRecordingController($variables);

        /** @var RouterRecordingController $controller */
        $controller = Router::execute('/posts/show/7/blue');

        $this->assertSame(
            ['initialize', 'before_filter', 'show', 'after_filter', 'finalize'],
            $controller->events
        );
        $this->assertSame(['7', 'blue'], $controller->received);
        $this->assertSame(1, $controller->actionCalls);
    }

    public static function filterCancellationProvider(): array
    {
        return [
            'initialize false' => [false, null, ['initialize']],
            'before filter false' => [null, false, ['initialize', 'before_filter']],
            'null does not cancel' => [null, null, ['initialize', 'before_filter', 'index', 'after_filter', 'finalize']],
        ];
    }

    /** Verifies only a strict false callback result cancels dispatch. */
    #[DataProvider('filterCancellationProvider')]
    public function testB5OnlyFalseCancelsDispatch(
        mixed $initializeResult,
        mixed $beforeResult,
        array $expectedEvents
    ): void {
        $this->useScenarioRouter();
        RouterScenarioRouter::$controllerFactory = static function (array $variables) use ($initializeResult, $beforeResult): Controller {
            $controller = new RouterRecordingController($variables);
            $controller->initializeResult = $initializeResult;
            $controller->beforeResult = $beforeResult;
            return $controller;
        };

        /** @var RouterRecordingController $controller */
        $controller = Router::execute('/');

        $this->assertSame($expectedEvents, $controller->events);
        $this->assertSame(in_array('index', $expectedEvents, true) ? 1 : 0, $controller->actionCalls);
    }

    public static function parameterLimitProvider(): array
    {
        return [
            'one required argument' => [['7'], true],
            'required and optional arguments' => [['7', 'blue'], true],
            'missing required argument' => [[], false],
            'too many arguments' => [['7', 'blue', 'extra'], false],
        ];
    }

    /** Verifies actions execute only when parameter counts match their declared range. */
    #[DataProvider('parameterLimitProvider')]
    public function testB6ParameterLimitsAcceptOnlyDeclaredRange(array $parameters, bool $executes): void
    {
        $this->useScenarioRouter();
        RouterScenarioRouter::$routes['/limited'] = [
            'controller' => 'limited',
            'action' => 'show',
            'parameters' => $parameters,
            'controller_path' => 'limited',
        ];
        $controller = null;
        RouterScenarioRouter::$controllerFactory = static function (array $variables) use (&$controller): Controller {
            return $controller = new RouterRecordingController($variables);
        };

        try {
            Router::execute('/limited');
            $this->assertTrue($executes, 'Expected an invalid parameter count to throw.');
        } catch (KumbiaException $exception) {
            $this->assertFalse($executes, 'A valid parameter count unexpectedly threw.');
            $this->assertInstanceOf(KumbiaException::class, $exception);
        }

        $this->assertInstanceOf(RouterRecordingController::class, $controller);
        /** @var RouterRecordingController $controller */
        $this->assertSame(['initialize', 'before_filter'], array_slice($controller->events, 0, 2));
        if ($executes) {
            $this->assertSame(1, $controller->actionCalls);
            $this->assertSame($parameters, $controller->received);
            $this->assertSame(['after_filter', 'finalize'], array_slice($controller->events, -2));
        } else {
            $this->assertSame(0, $controller->actionCalls);
            $this->assertSame(['initialize', 'before_filter'], $controller->events);
        }
    }

    public static function reservedActionProvider(): array
    {
        return [['k_callback'], ['K_CALLBACK']];
    }

    /** Verifies the reserved callback name cannot be dispatched as an action. */
    #[DataProvider('reservedActionProvider')]
    public function testB7ReservedCallbackCannotBeExecutedAsAnAction(string $action): void
    {
        $this->useScenarioRouter();
        RouterScenarioRouter::$routes['/reserved'] = [
            'controller' => 'reserved',
            'action' => $action,
            'controller_path' => 'reserved',
        ];
        $controller = null;
        RouterScenarioRouter::$controllerFactory = static function (array $variables) use (&$controller): Controller {
            return $controller = new RouterRecordingController($variables);
        };

        try {
            Router::execute('/reserved');
            $this->fail('Expected the reserved action to be rejected.');
        } catch (KumbiaException) {
            $this->assertInstanceOf(RouterRecordingController::class, $controller);
            /** @var RouterRecordingController $controller */
            $this->assertSame(['initialize', 'before_filter'], $controller->events);
            $this->assertSame(0, $controller->actionCalls);
        }
    }

    /** Verifies a missing action follows the controller's missing-action contract. */
    public function testB7MissingActionUsesControllerMissingActionContract(): void
    {
        $this->useScenarioRouter();
        RouterScenarioRouter::$routes['/missing-action'] = [
            'controller' => 'missing_action',
            'action' => 'does_not_exist',
            'controller_path' => 'missing_action',
        ];
        $controller = null;
        RouterScenarioRouter::$controllerFactory = static function (array $variables) use (&$controller): Controller {
            return $controller = new RouterRecordingController($variables);
        };

        try {
            Router::execute('/missing-action');
            $this->fail('Expected the missing action contract to throw.');
        } catch (KumbiaException) {
            $this->assertInstanceOf(RouterRecordingController::class, $controller);
            /** @var RouterRecordingController $controller */
            $this->assertSame(['initialize', 'before_filter'], $controller->events);
            $this->assertSame(0, $controller->actionCalls);
        }
    }

    /** Verifies an internal redirect completes the source then dispatches the destination once. */
    public function testC1InternalRedirectCompletesSourceThenDispatchesDestinationOnce(): void
    {
        $this->useScenarioRouter();
        RouterScenarioRouter::$routes['/source/redirect'] = [
            'controller' => 'source',
            'action' => 'redirect',
            'controller_path' => 'source',
        ];
        RouterScenarioRouter::$controllerFactory = static fn (array $variables): Controller => new RouterRecordingController($variables);

        $source = Router::execute('/source/redirect');
        $expectedDestination = [
            'module' => '',
            'controller' => 'destination',
            'action' => 'show',
            'parameters' => ['42'],
            'controller_path' => 'destination',
            'route' => '/source/redirect',
            'method' => 'GET',
        ];

        $this->assertSame('source', $source->controller_name);
        $this->assertSame([
            'source:initialize',
            'source:before_filter',
            'source:redirect',
            'source:after_filter',
            'source:finalize',
            'destination:initialize',
            'destination:before_filter',
            'destination:show',
            'destination:after_filter',
            'destination:finalize',
        ], RouterEventLog::$events);
        $this->assertCount(2, RouterScenarioRouter::$controllerVariables);
        $this->assertRouterState($expectedDestination);
    }

    /** Verifies non-internal route replacement updates state without dispatching. */
    public function testC2NonInternalRouteReplacementDoesNotDispatch(): void
    {
        Router::init('/original');
        Router::to([
            'controller' => 'replacement',
            'parameters' => ['7'],
        ]);

        $this->assertSame([
            'controller' => 'replacement',
            'parameters' => ['7'],
            'module' => '',
            'action' => 'index',
            'controller_path' => 'index',
            'route' => '/original',
            'method' => 'GET',
        ], Router::get());
        $this->assertSame([], RouterEventLog::$events);
    }

    /** Verifies successive requests do not retain state from earlier routes. */
    public function testC3SuccessiveRequestsDoNotLeakRouteState(): void
    {
        $this->useScenarioRouter();
        RouterScenarioRouter::$routes['/first'] = [
            'module' => 'router_module',
            'controller' => 'router_compound_name',
            'action' => 'show',
            'parameters' => ['9'],
            'controller_path' => 'router_module/router_compound_name',
        ];
        RouterScenarioRouter::$controllerFactory = static fn (array $variables): Controller => new RouterRecordingController($variables);

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $first = Router::execute('/first');
        $this->assertSame('router_module', $first->module_name);
        $this->assertSame(['9'], $first->parameters);

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $second = Router::execute('/');
        $expected = [
            'module' => '',
            'controller' => 'index',
            'action' => 'index',
            'parameters' => [],
            'controller_path' => 'index',
            'route' => '/',
            'method' => 'GET',
        ];

        $this->assertControllerRoute($second, $expected);
        $this->assertRouterState($expected);
    }
}
