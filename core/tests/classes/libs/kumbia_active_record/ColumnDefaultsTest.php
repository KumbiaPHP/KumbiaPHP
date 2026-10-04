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

/**
 * Classification of columns into not_null[] / _with_default[] in
 * KumbiaActiveRecord::_dump_info().
 *
 * describe_table() returns column defaults as strings, so a `NOT NULL DEFAULT 0`
 * column arrives as '0' and a `DEFAULT ''` column as '', both falsy in PHP.
 * Deciding by the truthiness of the default put those columns in not_null[] and
 * left them out of _with_default[], which affected two places:
 *
 *   1. save() requires every not_null[] column that is not in _with_default[],
 *      so it failed with "El campo X no puede ser nulo" even though the column
 *      had a perfectly valid default in the database.
 *   2. The INSERT builder emits DEFAULT for _with_default[] columns and NULL for
 *      the rest, so those columns received NULL on a NOT NULL column.
 *
 * What matters is whether the column DECLARES a default, not what that default
 * evaluates to: the absence of a default is expressed as Default === null.
 *
 * The classification block is replicated here because _dump_info() is protected
 * and needs a live database connection; these tests cover the decision itself.
 *
 * @category    Test
 * @package     Core
 */
class ColumnDefaultsTest extends PHPUnit\Framework\TestCase
{
    /**
     * @var array<int, string>
     */
    private array $notNull = [];

    /**
     * @var array<int, string>
     */
    private array $withDefault = [];

    protected function setUp(): void
    {
        $this->notNull = [];
        $this->withDefault = [];
    }

    /**
     * Mirrors the classification block of KumbiaActiveRecord::_dump_info().
     *
     * @param array<string, mixed> $field One row as returned by describe_table()
     */
    private function classify(array $field): void
    {
        $hasDefault = isset($field['Default']);
        if ($field['Null'] == 'NO' && !$hasDefault) {
            $this->notNull[] = $field['Field'];
        }
        if ($hasDefault) {
            $this->withDefault[] = $field['Field'];
        }
    }

    /**
     * @return array<string, array{0: array<string, mixed>}>
     */
    public static function columnsWithFalsyDefaultProvider(): array
    {
        return [
            'NOT NULL DEFAULT 0' => [
                ['Field' => 'enabled', 'Null' => 'NO', 'Default' => '0'],
            ],
            "NOT NULL DEFAULT ''" => [
                ['Field' => 'nickname', 'Null' => 'NO', 'Default' => ''],
            ],
            'NOT NULL DEFAULT 0.00' => [
                ['Field' => 'balance', 'Null' => 'NO', 'Default' => '0.00'],
            ],
        ];
    }

    /**
     * A falsy default is still a default: the column must not be reported as
     * required, otherwise save() rejects a record the database would accept.
     *
     * @param array<string, mixed> $field
     */
    #[DataProvider('columnsWithFalsyDefaultProvider')]
    public function testColumnWithFalsyDefaultIsNotRequired(array $field): void
    {
        $this->classify($field);

        $this->assertNotContains($field['Field'], $this->notNull);
    }

    /**
     * The same column must reach _with_default[], otherwise the INSERT builder
     * emits NULL instead of DEFAULT on a NOT NULL column.
     *
     * @param array<string, mixed> $field
     */
    #[DataProvider('columnsWithFalsyDefaultProvider')]
    public function testColumnWithFalsyDefaultUsesDatabaseDefault(array $field): void
    {
        $this->classify($field);

        $this->assertContains($field['Field'], $this->withDefault);
    }

    /**
     * A NOT NULL column without a default is the only case that is required.
     */
    public function testNotNullColumnWithoutDefaultIsRequired(): void
    {
        $this->classify(['Field' => 'email', 'Null' => 'NO', 'Default' => null]);

        $this->assertContains('email', $this->notNull);
        $this->assertNotContains('email', $this->withDefault);
    }

    /**
     * A truthy default keeps behaving as before: this is the case the original
     * condition already handled correctly.
     */
    public function testNotNullColumnWithTruthyDefaultIsNotRequired(): void
    {
        $this->classify(['Field' => 'status', 'Null' => 'NO', 'Default' => '1']);

        $this->assertNotContains('status', $this->notNull);
        $this->assertContains('status', $this->withDefault);
    }

    /**
     * A nullable column is never required, with or without a default.
     */
    public function testNullableColumnIsNeverRequired(): void
    {
        $this->classify(['Field' => 'deleted_at', 'Null' => 'YES', 'Default' => null]);

        $this->assertNotContains('deleted_at', $this->notNull);
        $this->assertNotContains('deleted_at', $this->withDefault);
    }

    /**
     * The source condition must keep deciding by presence, not by truthiness.
     * Guards against a regression to `isset($field['Default']) && $field['Default']`.
     */
    public function testSourceClassifiesByPresenceOfDefault(): void
    {
        $source = file_get_contents(
            __DIR__ . '/../../../../libs/kumbia_active_record/kumbia_active_record.php'
        );

        $this->assertIsString($source);
        $this->assertStringContainsString(
            "\$hasDefault = isset(\$field['Default']);",
            $source
        );
        $this->assertStringNotContainsString(
            "isset(\$field['Default']) && \$field['Default']",
            $source
        );
    }
}
