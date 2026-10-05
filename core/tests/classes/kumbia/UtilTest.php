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

it('converts strings to underscores', function ($original, $expected) {
    expect(Util::underscore($original))->toBe($expected);
})->with([
            ['Hello World', 'Hello_World'],
            ['', ''],
            ['-_ae123$%&', '-_ae123$%&'],
            [' ', '_'],
            ['  ', '__'],
            ['---', '---'],
            ['If you did not receive a copy of the license and are unable to', 'If_you_did_not_receive_a_copy_of_the_license_and_are_unable_to'],
        ]);
});

it('converts strings to dashes', function ($original, $expected) {
    expect(Util::dash($original))->toBe($expected);
})->with([
            ['Hello World', 'Hello-World'],
            ['', ''],
            ['-_ae123$%&', '-_ae123$%&'],
            [' ', '-'],
            ['  ', '--'],
            ['---', '---'],
            ['___', '___'],
            [
                'If you did not receive a copy of the license and are unable to',
                'If-you-did-not-receive-a-copy-of-the-license-and-are-unable-to',
            ],
]);

it('humanizes underscored and dashed strings', function ($original, $expected) {
    expect(Util::humanize($original))->toBe($expected);
})->with([
            ['Hello-World', 'Hello World'],
            ['Hello_World', 'Hello World'],
            ['Hello-World-Again', 'Hello World Again'],
            ['Hello_World_Again', 'Hello World Again'],
            ['Hello-World_Again', 'Hello World Again'],
            ['Hello_World-Again', 'Hello World Again'],
            ['hello-world-again', 'hello world again'],
            ['hello_world_again', 'hello world again'],
            ['hello-world_again', 'hello world again'],
            ['hello_world-again', 'hello world again'],
            [' hello ', ' hello '],
            ['  ', '  '],
            ['---', '   '],
            ['___', '   '],
            ['-__---___', '         '],
            [
                'If you-did_not receive a-copy of the-license_and_are unable to',
                'If you did not receive a copy of the license and are unable to',
            ],
]);

it('encloses comma-separated values in quotes', function ($original, $expected) {
    expect(Util::encomillar($original))->toBe($expected);
})->with([
            ['a,b,c', '"a","b","c"'],
            ['a, b, c', '"a"," b"," c"'],
            [' a , b , c ', '" a "," b "," c "'],
            ['hello , world,123', '"hello "," world","123"'],
            ['hello,world,123', '"hello","world","123"'],
            ['hello, world, 123', '"hello"," world"," 123"'],
            ['hello , world , 123', '"hello "," world "," 123"'],
            ['hello , world , 123 ', '"hello "," world "," 123 "'],
            ['hello , world , 123 ', '"hello "," world "," 123 "'],
            ['a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z', '"a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z"'],
            ['a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z,1,2,3,4,5,6,7,8,9,0', '"a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z","1","2","3","4","5","6","7","8","9","0"'],
]);

it('converts strings to camel case', function ($original, $expected, $expectedLowerCase) {
    expect(Util::camelcase($original))->toBe($expected);
    expect(Util::camelcase($original, true))->toBe($expectedLowerCase);
})->with([
            ['a_b_c', 'ABC', 'aBC'],
            ['users', 'Users', 'users'],
            ['table_name', 'TableName', 'tableName'],
            ['table__name', 'TableName', 'tableName'],
            ['table___name', 'TableName', 'tableName'],
            ['table_name1', 'TableName1', 'tableName1'],
            ['table_name_1', 'TableName1', 'tableName1'],
            ['table_1_name', 'Table1Name', 'table1Name'],
            ['table_1name', 'Table1name', 'table1name'],
            ['table1_name', 'Table1Name', 'table1Name'],
            ['table1_2name', 'Table12name', 'table12name'],
            ['table_1_2_name', 'Table12Name', 'table12Name'],
            ['table_12_name', 'Table12Name', 'table12Name'],
            ['table12_name', 'Table12Name', 'table12Name'],
            ['table12name', 'Table12name', 'table12name'],
]);

it('converts strings to snake case', function ($original, $expected) {
    expect(Util::smallcase($original))->toBe($expected);
})->with([
            ['ABC', 'a_b_c'],
            ['Users', 'users'],
            ['TableName', 'table_name'],
            ['TableName1', 'table_name1'],
            ['Table1Name', 'table1_name'],
            ['Table12name', 'table12name'],
            ['Table12Name', 'table12_name'],
]);

it('parses parameters', function ($original, $expected) {
    expect(Util::getParams($original))->toEqual($expected);
})->with([
            [[], []],
            [['a: b'], ['a' => 'b']],
            [['a: b', 'c: d'], ['a' => 'b', 'c' => 'd']],
            [['param1: value1', 'param2:  value2'], ['param1' => 'value1', 'param2' => ' value2']],
            [['param1 : value1', 'param2 :  value2'], ['param1 ' => 'value1', 'param2 ' => ' value2']],
            [['value1', 'value2'], ['value1', 'value2']],
            [['value1', 'value2', 'param1: value1', 'param2:  value2'], ['value1', 'value2', 'param1' => 'value1', 'param2' => ' value2']],
            [['value1', 'value2', 'param1 : value1', 'param2 :  value2'], ['value1', 'value2', 'param1 ' => 'value1', 'param2 ' => ' value2']],
            [['value1', 'value2', 'param1: value1', 'param2:  value2', 'value3'], ['value1', 'value2', 'param1' => 'value1', 'param2' => ' value2', 'value3']],
            [['value1', 'value2', 'param1 : value1', 'param2 :  value2', 'value3'], ['value1', 'value2', 'param1 ' => 'value1', 'param2 ' => ' value2', 'value3']],
            [['a: b'], ['a' => 'b']],
            [['a: b', 'c: d'], ['a' => 'b', 'c' => 'd']],
            [
                ['param1: value1', 'param2:  value2'],
                ['param1' => 'value1', 'param2' => ' value2']
            ]   ,
            [
                ['param1 : value1', 'param2 :  value2'],
                ['param1 ' => 'value1', 'param2 ' => ' value2']
            ],
            [
                ['value1', 'value2'],
                ['value1', 'value2'],
            ],
            [
                ['value1', 'value2', 'param1: value1', 'param2:  value2'],
                ['value1', 'value2', 'param1' => 'value1', 'param2' => ' value2']
            ],
            [
                ['value1', 'value2', 'param1 : value1', 'param2 :  value2'],
                ['value1', 'value2', 'param1 ' => 'value1', 'param2 ' => ' value2']
            ],
            [
                ['value1', 'value2', 'param1: value1', 'param2:  value2', 'value3'],
                ['value1', 'value2', 'param1' => 'value1', 'param2' => ' value2', 'value3']
            ],
            [
                ['value1', 'value2', 'param1 : value1', 'param2 :  value2', 'value3'],
                ['value1', 'value2', 'param1 ' => 'value1', 'param2 ' => ' value2', 'value3']
            ],
            [
                ['a: b'],
                ['a' => 'b']
            ],
            [
                ['a: b', 'c: d'],
                ['a' => 'b', 'c' => 'd']
            ],
            [
                ['param1: value1', 'param2:  value2'],
                ['param1' => 'value1', 'param2' => ' value2']
            ],
            [
                ['param1 : value1', 'param2 :  value2'],
                ['param1 ' => 'value1', 'param2 ' => ' value2']
            ],
            [
                ['value1', 'value2'],
                ['value1', 'value2']
            ],
            [
                ['value1', 'value2', 'param1: value1', 'param2:  value2'],
                ['value1', 'value2', 'param1' => 'value1', 'param2' => ' value2']
            ],
            [
                ['value1', 'value2', 'param1 : value1', 'param2 :  value2'],
                ['value1', 'value2', 'param1 ' => 'value1', 'param2 ' => ' value2']
            ],
            [
                ['value1', 'value2'],
                ['value1', 'value2']
            ],
        ],
    );
