<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use Mortezamasumi\FbProfile\Rules\IranNid;

function validateNid(string $value, bool $condition = true): bool
{
    $failed = false;

    (new IranNid($condition))->validate('nid', $value, function () use (&$failed) {
        $failed = true;
    });

    return ! $failed;
}

beforeEach(function () {
    App::shouldReceive('isProduction')->andReturn(true);
    config(['fb-profile.use_passport_number_on_nid' => false]);
});

it('accepts a valid Iranian NID', function () {
    expect(validateNid('0499370899'))->toBeTrue();
});

it('rejects an NID with a wrong checksum', function () {
    expect(validateNid('0499370890'))->toBeFalse();
});

it('rejects repeated-digit NIDs', function () {
    expect(validateNid('1111111111'))->toBeFalse()
        ->and(validateNid('0000000000'))->toBeFalse()
        ->and(validateNid('9999999999'))->toBeFalse();
});

it('rejects non-numeric input', function () {
    expect(validateNid('abc'))->toBeFalse();
});

it('accepts passport numbers when configured', function () {
    config(['fb-profile.use_passport_number_on_nid' => true]);

    expect(validateNid('1234567890'))->toBeTrue();
});

it('skips validation when the condition is false', function () {
    expect(validateNid('1111111111', condition: false))->toBeTrue();
});

it('registers the iran_nid validator rule', function () {
    Validator::make(
        ['nid' => '0499370899'],
        ['nid' => 'iran_nid']
    )->validate();

    expect(true)->toBeTrue();
});
