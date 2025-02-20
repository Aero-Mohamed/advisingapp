<?php

/*
<COPYRIGHT>

    Copyright © 2022-2023, Canyon GBS LLC. All rights reserved.

    Advising App™ is licensed under the Elastic License 2.0. For more details,
    see https://github.com/canyongbs/advisingapp/blob/main/LICENSE.

    Notice:

    - You may not provide the software to third parties as a hosted or managed
      service, where the service provides users with access to any substantial set of
      the features or functionality of the software.
    - You may not move, change, disable, or circumvent the license key functionality
      in the software, and you may not remove or obscure any functionality in the
      software that is protected by the license key.
    - You may not alter, remove, or obscure any licensing, copyright, or other notices
      of the licensor in the software. Any use of the licensor’s trademarks is subject
      to applicable law.
    - Canyon GBS LLC respects the intellectual property rights of others and expects the
      same in return. Canyon GBS™ and Advising App™ are registered trademarks of
      Canyon GBS LLC, and we are committed to enforcing and protecting our trademarks
      vigorously.
    - The software solution, including services, infrastructure, and code, is offered as a
      Software as a Service (SaaS) by Canyon GBS LLC.
    - Use of this software implies agreement to the license terms and conditions as stated
      in the Elastic License 2.0.

    For more information or inquiries please visit our website at
    https://www.canyongbs.com or contact us via email at legal@canyongbs.com.

</COPYRIGHT>
*/

use App\Models\User;

use function Tests\asSuperAdmin;
use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

use Lab404\Impersonate\Services\ImpersonateManager;
use STS\FilamentImpersonate\Tables\Actions\Impersonate;
use App\Filament\Resources\UserResource\Pages\ListUsers;

it('renders impersonate button for non super admin users when user is super admin', function () {
    asSuperAdmin();

    $user = User::factory()->create();

    $component = livewire(ListUsers::class);

    $component
        ->assertSuccessful()
        ->assertCountTableRecords(2)
        ->assertTableActionVisible(Impersonate::class, $user);
});

it('does not render impersonate button for super admin users when user is not super admin', function () {
    $superAdmin = User::factory()->create();
    asSuperAdmin($superAdmin);

    $user = User::factory()
        ->create()
        ->givePermissionTo('user.view-any', 'user.*.view');
    actingAs($user);

    $component = livewire(ListUsers::class);

    $component
        ->assertSuccessful()
        ->assertCountTableRecords(2)
        ->assertTableActionHidden(Impersonate::class, $superAdmin);
});

it('does not render impersonate button for super admin users at all', function () {
    $superAdmin = User::factory()->create();
    asSuperAdmin($superAdmin);

    $user = User::factory()->create();
    asSuperAdmin($user);

    $component = livewire(ListUsers::class);

    $component
        ->assertSuccessful()
        ->assertCountTableRecords(2)
        ->assertTableActionHidden(Impersonate::class, $superAdmin);
});

it('does not render impersonate button for super admin users even if user has permission', function () {
    $superAdmin = User::factory()->create();
    asSuperAdmin($superAdmin);

    $user = User::factory()
        ->create()
        ->givePermissionTo('user.view-any', 'user.*.view', 'authorization.impersonate');

    actingAs($user);

    $component = livewire(ListUsers::class);

    $component
        ->assertSuccessful()
        ->assertCountTableRecords(2)
        ->assertTableActionHidden(Impersonate::class, $superAdmin);
});

it('allows super admin user to impersonate', function () {
    $superAdmin = User::factory()->create();
    asSuperAdmin($superAdmin);

    $user = User::factory()->create();

    $component = livewire(ListUsers::class);

    $component
        ->assertSuccessful()
        ->assertCountTableRecords(2)
        ->callTableAction(Impersonate::class, $user);

    expect($user->isImpersonated())->toBeTrue();
    expect(auth()->id())->toBe($user->id);
});

it('allows user with permission to impersonate', function () {
    $first = User::factory()->create();
    $first->givePermissionTo('user.view-any', 'user.*.view', 'authorization.impersonate');
    actingAs($first);

    $second = User::factory()->create();

    $component = livewire(ListUsers::class);

    $component
        ->assertSuccessful()
        ->assertCountTableRecords(2)
        ->callTableAction(Impersonate::class, $second);

    expect($second->isImpersonated())->toBeTrue();
    expect(auth()->id())->toBe($second->id);
});

it('allows a user to leave impersonate', function () {
    $first = User::factory()->create();
    $first->givePermissionTo('authorization.impersonate');
    actingAs($first);

    $second = User::factory()->create();

    app(ImpersonateManager::class)->take($first, $second);

    expect($second->isImpersonated())->toBeTrue();
    expect(auth()->id())->toBe($second->id);

    $second->leaveImpersonation();

    expect($second->isImpersonated())->toBeFalse();
    expect(auth()->id())->toBe($first->id);
});
