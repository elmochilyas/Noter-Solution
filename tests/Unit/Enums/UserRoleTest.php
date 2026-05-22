<?php

use App\Enums\UserRole;

test('UserRole has owner and assistant cases', function () {
    expect(UserRole::OWNER->value)->toBe('owner');
    expect(UserRole::ASSISTANT->value)->toBe('assistant');
});
