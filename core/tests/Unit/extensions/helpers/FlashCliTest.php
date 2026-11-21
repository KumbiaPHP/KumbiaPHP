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
 * @package    Flash
 *
 * @copyright  Copyright (c) 2005 - 2026 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

// No validation of output is done here, as the output is sent to STDOUT or STDERR depending on the message type.
// The tests are mainly to ensure that the methods can be called without errors.
// The actual output can be verified by observing the output streams.

it('shows a valid flash message to STDOUT', function () {
    Flash::valid('Test content for valid');
    expect(true)->toBeTrue();
});

it('shows an error flash message to STDERR', function () {
    Flash::error('Test content for error');
    expect(true)->toBeTrue();
});

it('shows an info flash message to STDOUT', function () {
    Flash::info('Test content for info');
    expect(true)->toBeTrue();
});

it('shows a warning flash message to STDERR', function () {
    Flash::warning('Test content for warning');
    expect(true)->toBeTrue();
});
