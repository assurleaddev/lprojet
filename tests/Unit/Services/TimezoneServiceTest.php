<?php

declare(strict_types=1);

use App\Services\TimezoneService;

test('get timezones returns a non empty identifier to label map', function () {
    $timezones = (new TimezoneService())->getTimezones();

    expect($timezones)->toBeArray()->not->toBeEmpty()
        ->and($timezones['UTC'])->toEqual('(UTC+00:00) UTC')
        ->and($timezones['America/Los_Angeles'])->toEqual('(UTC-08:00) Pacific Time (US & Canada)');
});

test('every timezone key is a valid php timezone identifier', function () {
    $identifiers = \DateTimeZone::listIdentifiers(\DateTimeZone::ALL_WITH_BC);

    foreach (array_keys((new TimezoneService())->getTimezones()) as $timezone) {
        expect($identifiers)->toContain($timezone);
    }
});

test('every timezone label starts with a utc offset', function () {
    foreach ((new TimezoneService())->getTimezones() as $label) {
        expect($label)->toMatch('/^\(UTC[+-]\d{2}:\d{2}\) .+/');
    }
});
