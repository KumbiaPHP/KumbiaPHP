<?php
/**
 * KumbiaPHP web & app Framework
 *
 * @category   Test
 * @package    Db
 * @subpackage Adapters
 */

class PgsqlDescribeTableIntegrationTest extends PHPUnit\Framework\TestCase
{
    private $native;
    private $pdo;
    private $schema;
    private $table;

    protected function setUp(): void
    {
        if (!extension_loaded('pgsql')) {
            $this->markTestSkipped('PostgreSQL integration requires the pgsql extension.');
        }
        if (!extension_loaded('pdo_pgsql')) {
            $this->markTestSkipped('PostgreSQL integration requires the pdo_pgsql extension.');
        }

        $config = $this->configuration();

        require_once CORE_PATH.'libs/db/db_base.php';
        require_once CORE_PATH.'libs/db/db_base_interface.php';
        require_once CORE_PATH.'libs/db/adapters/pdo/interface.php';
        require_once CORE_PATH.'libs/db/adapters/pdo.php';
        require_once CORE_PATH.'libs/db/adapters/pgsql.php';
        require_once CORE_PATH.'libs/db/adapters/pdo/pgsql.php';

        try {
            $this->native = new DbPgSQL($config);
            $this->pdo = new DbPdoPgSQL([
                'type' => 'pgsql',
                'dsn' => "host={$config['host']};port={$config['port']};dbname={$config['name']}",
                'username' => $config['username'],
                'password' => $config['password'],
            ]);
        } catch (Throwable $exception) {
            $this->markTestSkipped('PostgreSQL integration runtime is unavailable: '.$exception->getMessage());
        }

        $suffix = strtolower(dechex(getmypid()).dechex(random_int(0, PHP_INT_MAX)));
        $this->schema = 'kumbia_issue118_'.$suffix;
        $this->table = 'schema_metadata_'.$suffix;

        try {
            $this->native->query("CREATE SCHEMA {$this->schema}");
            $this->native->query("CREATE TABLE public.{$this->table} (public_id integer PRIMARY KEY, public_only text)");
            $this->native->query("CREATE TABLE {$this->schema}.{$this->table} (custom_id integer PRIMARY KEY, custom_only text)");
        } catch (Throwable $exception) {
            $this->cleanup();
            $this->markTestSkipped('PostgreSQL integration fixtures cannot be created: '.$exception->getMessage());
        }
    }

    protected function tearDown(): void
    {
        $this->cleanup();
    }

    public function testBothAdaptersIsolatePublicAndCustomSchemaMetadata()
    {
        foreach ([$this->native, $this->pdo] as $adapter) {
            $this->assertSame(['custom_id', 'custom_only'], $this->fields($adapter->describe_table($this->table, $this->schema)));
            $this->assertSame(['public_id', 'public_only'], $this->fields($adapter->describe_table($this->table)));
            $this->assertSame(['public_id', 'public_only'], $this->fields($adapter->describe_table($this->table, null)));
            $this->assertSame(['public_id', 'public_only'], $this->fields($adapter->describe_table($this->table, '')));
            $this->assertSame([], $adapter->describe_table($this->table, 'kumbia_issue118_missing'));
        }
    }

    private function configuration()
    {
        $required = ['PGHOST', 'PGDATABASE', 'PGUSER'];
        $missing = [];
        foreach ($required as $name) {
            if (getenv($name) === false || getenv($name) === '') {
                $missing[] = $name;
            }
        }
        if ($missing) {
            $this->markTestSkipped('PostgreSQL integration credentials are unavailable: '.implode(', ', $missing).'.');
        }

        return [
            'host' => getenv('PGHOST'),
            'port' => getenv('PGPORT') ?: 5432,
            'name' => getenv('PGDATABASE'),
            'username' => getenv('PGUSER'),
            'password' => getenv('PGPASSWORD') ?: '',
        ];
    }

    private function fields($metadata)
    {
        return array_column($metadata, 'Field');
    }

    private function cleanup()
    {
        if (!$this->native || !$this->schema || !$this->table) {
            return;
        }

        try {
            $this->native->query("DROP TABLE IF EXISTS public.{$this->table}");
            $this->native->query("DROP SCHEMA IF EXISTS {$this->schema} CASCADE");
        } catch (Throwable $exception) {
        }
    }
}
