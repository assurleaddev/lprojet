<?php

declare(strict_types=1);

use App\Services\PasswordService;

beforeEach(function () {
    $this->service = new PasswordService();
});

test('generate password appends a special character to the requested length', function () {
    expect(strlen($this->service->generatePassword()))->toEqual(13)
        ->and(strlen($this->service->generatePassword(20)))->toEqual(21);
});

test('generate password can omit special characters', function () {
    $password = $this->service->generatePassword(16, false);

    expect(strlen($password))->toEqual(16)
        ->and($password)->toMatch('/^[A-Za-z0-9]+$/');
});

test('generate password includes at least one special character by default', function () {
    expect($this->service->generatePassword(8))->toMatch('/[!@#$%^&*()\-_=+\[\]{}|;:,.<>?]/');
});

test('generate password returns a different value on each call', function () {
    expect($this->service->generatePassword())->not->toEqual($this->service->generatePassword());
});
