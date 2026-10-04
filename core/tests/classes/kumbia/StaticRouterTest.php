<?php

use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

require_once dirname(__DIR__, 2).'/support/RouterTestCase.php';

class PersistentRouterController extends RouterRecordingController
{
    public static array $constructedVariables = [];

    public function __construct(array $args)
    {
        self::$constructedVariables[] = $args;
        parent::__construct($args);
    }
}

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class StaticRouterTest extends RouterTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        PersistentRouterController::$constructedVariables = [];
        $this->useScenarioRouter();
        RouterScenarioRouter::$controllerFactory = static fn (array $variables): Controller => new PersistentRouterController($variables);
    }

    /** Verifies a cache hit creates a fresh controller with the current request method. */
    public function testD3CacheHitCreatesFreshControllerAndRefreshesMethod(): void
    {
        RouterScenarioRouter::$routes['/persistent'] = [
            'controller' => 'persistent',
            'action' => 'index',
            'controller_path' => 'persistent',
        ];

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $first = StaticRouter::execute('/persistent');
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $second = StaticRouter::execute('/persistent');

        $this->assertNotSame($first, $second);
        $this->assertCount(1, RouterScenarioRouter::$rewrites);
        $this->assertCount(1, RouterScenarioRouter::$controllerVariables);
        $this->assertCount(2, PersistentRouterController::$constructedVariables);
        $this->assertSame('GET', PersistentRouterController::$constructedVariables[0]['method']);
        $this->assertSame('POST', PersistentRouterController::$constructedVariables[1]['method']);
        $this->assertSame(1, $first->actionCalls);
        $this->assertSame(1, $second->actionCalls);
        $this->assertRouterState([
            'module' => '',
            'controller' => 'persistent',
            'action' => 'index',
            'parameters' => [],
            'controller_path' => 'persistent',
            'route' => '/persistent',
            'method' => 'POST',
        ]);
    }

    /** Verifies cached routes restore their own variables across an A-to-B-to-A sequence. */
    public function testD4CacheRestoresEachRoutesOwnVariablesAcrossAtoBtoA(): void
    {
        RouterScenarioRouter::$routes = [
            '/persistent-a' => [
                'controller' => 'persistent_a',
                'action' => 'show',
                'parameters' => ['a-value'],
                'controller_path' => 'persistent_a',
            ],
            '/persistent-b' => [
                'module' => 'router_module',
                'controller' => 'persistent_b',
                'action' => 'show',
                'parameters' => ['b-value'],
                'controller_path' => 'router_module/persistent_b',
            ],
        ];

        $firstA = StaticRouter::execute('/persistent-a');
        $controllerB = StaticRouter::execute('/persistent-b');
        $finalA = StaticRouter::execute('/persistent-a');

        $this->assertNotSame($firstA, $finalA);
        $this->assertSame(['a-value'], $firstA->received);
        $this->assertSame(['b-value'], $controllerB->received);
        $this->assertSame(['a-value'], $finalA->received);
        $this->assertSame('', $finalA->module_name);
        $this->assertCount(2, RouterScenarioRouter::$rewrites);
        $this->assertCount(2, RouterScenarioRouter::$controllerVariables);
        $this->assertRouterState([
            'module' => '',
            'controller' => 'persistent_a',
            'action' => 'show',
            'parameters' => ['a-value'],
            'controller_path' => 'persistent_a',
            'route' => '/persistent-a',
            'method' => 'GET',
        ]);
    }
}
