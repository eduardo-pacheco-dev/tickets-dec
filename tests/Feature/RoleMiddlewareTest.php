<?php

use App\Models\User;

it('allows admin users to access admin ticket routes', function () {
    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    $this->get(route('admin.tickets.index'))->assertOk();
});

it('allows operator users to access admin ticket routes', function () {
    $user = User::factory()->operator()->create();
    $this->actingAs($user);

    $this->get(route('admin.tickets.index'))->assertOk();
});

it('allows supervisor users to access admin ticket routes', function () {
    $user = User::factory()->supervisor()->create();
    $this->actingAs($user);

    $this->get(route('admin.tickets.index'))->assertOk();
});

it('denies client users from admin ticket routes', function () {
    $user = User::factory()->client()->create();
    $this->actingAs($user);

    $this->get(route('admin.tickets.index'))->assertForbidden();
});

it('allows only admin users to access user management routes', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $this->get(route('admin.users.index'))->assertOk();
});

it('denies operator users from user management routes', function () {
    $user = User::factory()->operator()->create();
    $this->actingAs($user);

    $this->get(route('admin.users.index'))->assertForbidden();
});

it('denies supervisor users from user management routes', function () {
    $user = User::factory()->supervisor()->create();
    $this->actingAs($user);

    $this->get(route('admin.users.index'))->assertForbidden();
});

it('denies client users from user management routes', function () {
    $user = User::factory()->client()->create();
    $this->actingAs($user);

    $this->get(route('admin.users.index'))->assertForbidden();
});

it('redirects guests from user management routes to login', function () {
    $this->get(route('admin.users.index'))->assertRedirect(route('login'));
});
