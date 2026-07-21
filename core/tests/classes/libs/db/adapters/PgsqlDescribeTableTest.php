<?php
/**
 * KumbiaPHP web & app Framework
 *
 * @category   Test
 * @package    Db
 * @subpackage Adapters
 */

require_once CORE_PATH.'libs/db/db_base.php';
require_once CORE_PATH.'libs/db/db_base_interface.php';
require_once CORE_PATH.'libs/db/adapters/pdo/interface.php';
require_once CORE_PATH.'libs/db/adapters/pdo.php';

if (!defined('PGSQL_CONNECT_FORCE_NEW')) {
    define('PGSQL_CONNECT_FORCE_NEW', 2);
}

require_once CORE_PATH.'libs/db/adapters/pgsql.php';
require_once CORE_PATH.'libs/db/adapters/pdo/pgsql.php';

class PgsqlDescribeTableNativeDouble extends DbPgSQL
{
    public $queries = [];
    public $rows = null;

    public function __construct()
    {
    }

    public function fetch_all($sql)
    {
        $this->queries[] = $sql;

        return $this->rows === null ? PgsqlDescribeTableTest::metadataRows() : $this->rows;
    }
}

class PgsqlDescribeTablePdoDouble extends DbPdoPgSQL
{
    public $queries = [];
    public $rows = null;

    public function __construct()
    {
    }

    public function fetch_all($sql)
    {
        $this->queries[] = $sql;

        return $this->rows === null ? PgsqlDescribeTableTest::metadataRows() : $this->rows;
    }
}

class PgsqlDescribeTableTest extends PHPUnit\Framework\TestCase
{
    public static function metadataRows()
    {
        return [
            [
                'field' => 'id',
                'type' => 'int4',
                'null' => 'NO',
                'key' => 'PRI',
                'default' => 'nextval(\'orders_id_seq\'::regclass)',
            ],
        ];
    }

    public function testBothAdaptersSelectOnlyTheExplicitSchema()
    {
        foreach ($this->adapters() as $adapter) {
            $adapter->describe_table('orders', 'reporting');
            $sql = $adapter->queries[0];

            $this->assertStringContainsString('pg_catalog.pg_namespace n', $sql);
            $this->assertStringContainsString('n.oid = c.relnamespace', $sql);
            $this->assertStringContainsString("n.nspname = 'reporting'", $sql);
        }
    }

    public function testBothAdaptersDefaultOmittedSchemaToPublic()
    {
        foreach ($this->adapters() as $adapter) {
            $adapter->describe_table('orders');

            $this->assertStringContainsString("n.nspname = 'public'", $adapter->queries[0]);
        }
    }

    /**
     * @dataProvider emptySchemaInputs
     */
    public function testBothAdaptersDefaultNullAndEmptySchemaToPublic($schema)
    {
        foreach ($this->adapters() as $adapter) {
            $adapter->describe_table('orders', $schema);

            $this->assertStringContainsString("n.nspname = 'public'", $adapter->queries[0]);
        }
    }

    public function emptySchemaInputs()
    {
        return [
            'null schema' => [null],
            'empty schema' => [''],
        ];
    }

    public function testNamespacePredicatePreventsFallbackToAnotherSchemasTable()
    {
        foreach ($this->adapters() as $adapter) {
            $adapter->describe_table('orders', 'missing_schema');
            $sql = $adapter->queries[0];

            $this->assertStringContainsString("c.relname = 'orders'", $sql);
            $this->assertStringContainsString("n.nspname = 'missing_schema'", $sql);
            $this->assertStringNotContainsString('OR n.nspname', $sql);
        }
    }

    public function testBothAdaptersEscapeQuotesInTheSchemaPredicate()
    {
        foreach ($this->adapters() as $adapter) {
            $adapter->describe_table('orders', "reporting' OR true --");
            $sql = $adapter->queries[0];

            $this->assertStringContainsString("n.nspname = 'reporting'' OR true --'", $sql);
            $this->assertStringNotContainsString("n.nspname = 'reporting' OR true --", $sql);
        }
    }

    public function testBothAdaptersReturnAnEmptyArrayWhenTheRequestedSchemaHasNoTable()
    {
        foreach ($this->adapters() as $adapter) {
            $adapter->rows = [];

            $this->assertSame([], $adapter->describe_table('orders', 'missing_schema'));
        }
    }

    public function testBothAdaptersPreserveTheExistingMetadataShape()
    {
        foreach ($this->adapters() as $adapter) {
            $this->assertSame([
                [
                    'Field' => 'id',
                    'Type' => 'int4',
                    'Null' => 'NO',
                    'Key' => 'PRI',
                    'Default' => 'nextval(\'orders_id_seq\'::regclass)',
                ],
            ], $adapter->describe_table('orders', 'reporting'));
        }
    }

    private function adapters()
    {
        return [
            new PgsqlDescribeTableNativeDouble(),
            new PgsqlDescribeTablePdoDouble(),
        ];
    }
}
