<?php

use App\Enums\Role;

test('i ruoli sono gerarchici', function () {
    expect(Role::SuperAdmin->isAtLeast(Role::Admin))->toBeTrue()
        ->and(Role::Admin->isAtLeast(Role::Staff))->toBeTrue()
        ->and(Role::Staff->isAtLeast(Role::Staff))->toBeTrue()
        ->and(Role::Student->isAtLeast(Role::Staff))->toBeFalse();
});
