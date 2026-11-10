<?php

use App\Rules\ValidGstin;
use App\Services\Gst\GstCalculator;
use App\Services\Gst\InterStateGst;
use App\Services\Gst\IntraStateGst;

function gstCalculator(): GstCalculator
{
    return new GstCalculator(new IntraStateGst, new InterStateGst);
}

function gstinErrors(string $value): array
{
    $errors = [];

    (new ValidGstin)->validate('gstin', $value, function ($message) use (&$errors) {
        $errors[] = $message;
    });

    return $errors;
}

it('splits tax into CGST and SGST within the same state', function () {
    $tax = gstCalculator()->calculate('27', '27', 100_000, 1_800);

    expect($tax->cgstPaise)->toBe(9_000)
        ->and($tax->sgstPaise)->toBe(9_000)
        ->and($tax->igstPaise)->toBe(0);
});

it('charges IGST across different states', function () {
    $tax = gstCalculator()->calculate('27', '29', 100_000, 1_800);

    expect($tax->igstPaise)->toBe(18_000)
        ->and($tax->cgstPaise)->toBe(0);
});

it('rounds each tax component half up to the nearest paisa', function () {
    $intra = gstCalculator()->calculate('27', '27', 10_050, 1_800);
    $inter = gstCalculator()->calculate('27', '29', 10_050, 1_800);

    expect($intra->totalTaxPaise())->toBe(1_810)
        ->and($inter->totalTaxPaise())->toBe(1_809);
});

it('accepts a valid GSTIN in any letter case', function () {
    expect(gstinErrors('27AAPFU0939F1ZV'))->toBeEmpty()
        ->and(gstinErrors('27aapfu0939f1zv'))->toBeEmpty();
});

it('rejects invalid GSTINs', function (string $gstin) {
    expect(gstinErrors($gstin))->not->toBeEmpty();
})->with([
    'wrong checksum' => '27AAPFU0939F1ZX',
    'too short' => '27AAPFU0939F1Z',
    'wrong format' => 'ABCDEFGHIJKLMNO',
    'empty' => '',
]);
