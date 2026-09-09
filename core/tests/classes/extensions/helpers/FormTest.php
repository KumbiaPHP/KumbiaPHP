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
 * @package    Form
 *
 * @copyright  Copyright (c) 2005 - 2026 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

require_once CORE_PATH.'kumbia/kumbia_view.php';

if (!class_exists('View', false)) {
    class View extends KumbiaView
    {
    }
}

class FormTest extends PHPUnit\Framework\TestCase
{
    private $originalPost;
    private $originalRadios;
    private $originalViewData;
    private $radios;
    private $viewData;

    protected function setUp(): void
    {
        $this->originalPost = $_POST;
        $this->radios = new ReflectionProperty(Form::class, 'radios');
        $this->radios->setAccessible(true);
        $this->originalRadios = $this->radios->getValue();
        $this->viewData = new ReflectionProperty(View::class, '_controller');
        $this->viewData->setAccessible(true);
        $this->originalViewData = $this->viewData->getValue();
        $this->radios->setValue(null, []);
        $_POST = [];
    }

    protected function tearDown(): void
    {
        $_POST = $this->originalPost;
        $this->radios->setValue(null, $this->originalRadios);
        $this->viewData->setValue(null, $this->originalViewData);
    }

    public static function checkedFieldProvider()
    {
        $cases = [
            [1, false, null, 1, false, true],
            [2, false, null, 1, true, false],
            [0, false, null, 0, false, true],
            ['0', false, null, '0', false, true],
            [false, false, null, 0, false, true],
            [null, false, null, 1, false, false],
            [null, false, null, 1, true, true],
            [2, true, 1, 1, false, true],
            [1, true, 2, 1, true, false],
            [1, true, '0', '0', false, true],
        ];
        $data = [];
        foreach (['check', 'radio'] as $helper) {
            foreach ($cases as $case) {
                $data[] = array_merge([$helper], $case);
            }
        }

        return $data;
    }

    /**
     * @dataProvider checkedFieldProvider
     */
    public function testCheckedFieldsUsePostThenModelThenFallback(
        $helper,
        $modelValue,
        $hasPost,
        $postValue,
        $checkValue,
        $fallback,
        $expected
    ) {
        $this->setModelValue($modelValue);
        if ($hasPost) {
            $_POST = ['record' => ['flag' => $postValue]];
        }

        $html = Form::$helper('record.flag', $checkValue, '', $fallback);

        $this->assertSame($expected, str_contains($html, 'checked="checked"'));
    }

    public static function twoPartViewValueProvider()
    {
        return [
            'object property' => [true, (object) ['flag' => 'object value'], 'object value'],
            'array property' => [true, ['flag' => 'array value'], 'array value'],
            'scalar string zero' => [true, '0', '0'],
            'scalar integer zero' => [true, 0, 0],
            'scalar false' => [true, false, false],
            'null view value' => [true, null, 'fallback'],
            'missing view value' => [false, null, 'fallback'],
            'null property' => [true, (object) ['flag' => null], 'fallback'],
            'missing object property' => [true, (object) [], 'fallback'],
            'missing array property' => [true, [], 'fallback'],
        ];
    }

    /**
     * @dataProvider twoPartViewValueProvider
     */
    public function testTwoPartFieldUsesViewValueOrFallback($hasViewValue, $viewValue, $expected)
    {
        $this->viewData->setValue(null, $hasViewValue ? ['record' => $viewValue] : []);

        [, , $actual] = Form::getFieldData('record.flag', 'fallback', false);

        $this->assertSame($expected, $actual);
    }

    public function testGenericFieldUsesPostedStringZero()
    {
        $this->setModelValue('model');
        $_POST = ['record' => ['flag' => '0']];

        [, , $actual] = Form::getFieldData('record.flag', 'fallback', false);

        $this->assertSame('0', $actual);
    }

    public static function onePartScalarViewValueProvider()
    {
        return [
            ['active'],
            ['0'],
            [0],
            [false],
        ];
    }

    /**
     * @dataProvider onePartScalarViewValueProvider
     */
    public function testOnePartFieldPreservesScalarViewValues($modelValue)
    {
        $this->viewData->setValue(null, ['status' => $modelValue]);

        [, , $actual] = Form::getFieldData('status', 'fallback', false);

        $this->assertSame($modelValue, $actual);
    }

    /**
     * @dataProvider onePartNonScalarValueProvider
     */
    public function testOnePartFieldIgnoresObjectAndArrayViewValues($modelValue)
    {
        $this->viewData->setValue(null, ['status' => $modelValue]);

        [, , $actual] = Form::getFieldData('status', 'fallback', false);

        $this->assertSame('fallback', $actual);
    }

    public static function onePartNonScalarValueProvider()
    {
        return [
            [(object) ['value' => 'ignored']],
            [['value' => 'ignored']],
        ];
    }

    public function testOnePartFieldUsesFallbackForNullOrUnavailableViewValue()
    {
        $this->viewData->setValue(null, ['status' => null]);
        [, , $nullValue] = Form::getFieldData('status', 'fallback', false);

        $this->viewData->setValue(null, []);
        [, , $unavailableValue] = Form::getFieldData('status', 'fallback', false);

        $this->assertSame('fallback', $nullValue);
        $this->assertSame('fallback', $unavailableValue);
    }

    public function testOnePartCheckedFieldUsesFallbackWithoutModelProperty()
    {
        $this->viewData->setValue(null, ['record' => (object) ['flag' => 1]]);

        [, , $actual] = Form::getFieldDataCheck('record', 1, true);

        $this->assertTrue($actual);
    }

    public function testRadioIdsStartFromZeroForEachTest()
    {
        $this->setModelValue(1);

        $first = Form::radio('record.flag', 1);
        $second = Form::radio('record.flag', 1);

        $this->assertStringContainsString('id="record_flag0"', $first);
        $this->assertStringContainsString('id="record_flag1"', $second);
    }

    private function setModelValue($value)
    {
        $this->viewData->setValue(null, ['record' => (object) ['flag' => $value]]);
    }
}
