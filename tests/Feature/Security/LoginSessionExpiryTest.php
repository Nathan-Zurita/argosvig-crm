<?php

it('reopens the authentication form after a stale submission', function (string $submitRoute, string $formRoute) {
    config(['app.debug' => false]);

    // Laravel skips CSRF validation in the testing environment.
    app()->instance('env', 'local');

    $this->withSession(['_token' => 'current-token'])
        ->post(route($submitRoute), ['_token' => 'stale-token'])
        ->assertRedirect(route($formRoute));

    $this->assertGuest('user');
})->with([
    'login' => ['admin.session.store', 'admin.session.create'],
    'two-factor verification' => ['admin.session.two_factor.verify', 'admin.session.two_factor'],
]);
