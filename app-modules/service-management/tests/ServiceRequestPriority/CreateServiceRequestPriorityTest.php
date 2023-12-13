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

use AdvisingApp\ServiceManagement\Filament\Resources\ServiceRequestPriorityResource\Pages\CreateServiceRequestPriority;
use App\Models\User;

use App\Settings\LicenseSettings;
use function Tests\asSuperAdmin;
use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEmpty;
use function Pest\Laravel\assertDatabaseHas;

use AdvisingApp\ServiceManagement\Models\ServiceRequestPriority;
use AdvisingApp\ServiceManagement\Filament\Resources\ServiceRequestPriorityResource;
use AdvisingApp\ServiceManagement\Tests\RequestFactories\CreateServiceRequestPriorityRequestFactory;

test('A successful action on the CreateServiceRequestPriority page', function () {
    asSuperAdmin()
        ->get(
            ServiceRequestPriorityResource::getUrl('create')
        )
        ->assertSuccessful();

    $request = CreateServiceRequestPriorityRequestFactory::new()->create();

    livewire(ServiceRequestPriorityResource\Pages\CreateServiceRequestPriority::class)
        ->fillForm($request)
        ->call('create')
        ->assertHasNoFormErrors();

    assertCount(1, ServiceRequestPriority::all());

    assertDatabaseHas(ServiceRequestPriority::class, $request);
});

test('CreateServiceRequestPriority requires valid data', function ($data, $errors) {
    asSuperAdmin();

    livewire(ServiceRequestPriorityResource\Pages\CreateServiceRequestPriority::class)
        ->fillForm(CreateServiceRequestPriorityRequestFactory::new($data)->create())
        ->call('create')
        ->assertHasFormErrors($errors);

    assertEmpty(ServiceRequestPriority::all());
})->with(
    [
        'name missing' => [CreateServiceRequestPriorityRequestFactory::new()->without('name'), ['name' => 'required']],
        'name not a string' => [CreateServiceRequestPriorityRequestFactory::new()->state(['name' => 1]), ['name' => 'string']],
        'order missing' => [CreateServiceRequestPriorityRequestFactory::new()->without('order'), ['order' => 'required']],
        'order not a number' => [CreateServiceRequestPriorityRequestFactory::new()->state(['order' => 'a']), ['order' => 'numeric']],
    ]
);

// Permission Tests

test('CreateServiceRequestPriority is gated with proper access control', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get(
            ServiceRequestPriorityResource::getUrl('create')
        )->assertForbidden();

    livewire(ServiceRequestPriorityResource\Pages\CreateServiceRequestPriority::class)
        ->assertForbidden();

    $user->givePermissionTo('service_request_priority.view-any');
    $user->givePermissionTo('service_request_priority.create');

    actingAs($user)
        ->get(
            ServiceRequestPriorityResource::getUrl('create')
        )->assertSuccessful();

    $request = collect(CreateServiceRequestPriorityRequestFactory::new()->create());

    livewire(ServiceRequestPriorityResource\Pages\CreateServiceRequestPriority::class)
        ->fillForm($request->toArray())
        ->call('create')
        ->assertHasNoFormErrors();

    assertCount(1, ServiceRequestPriority::all());

    assertDatabaseHas(ServiceRequestPriority::class, $request->toArray());
});

test('CreateServiceRequestPriority is gated with proper feature access control', function () {
    $settings = app(LicenseSettings::class);

    $settings->data->addons->serviceManagement = false;

    $settings->save();

    $user = User::factory()->create();

    $user->givePermissionTo('service_request_priority.view-any');
    $user->givePermissionTo('service_request_priority.create');

    actingAs($user)
        ->get(
            ServiceRequestPriorityResource::getUrl('create')
        )->assertForbidden();

    livewire(CreateServiceRequestPriority::class)
        ->assertForbidden();

    $settings->data->addons->serviceManagement = true;

    $settings->save();

    actingAs($user)
        ->get(
            ServiceRequestPriorityResource::getUrl('create')
        )->assertSuccessful();

    $request = collect(CreateServiceRequestPriorityRequestFactory::new()->create());

    livewire(CreateServiceRequestPriority::class)
        ->fillForm($request->toArray())
        ->call('create')
        ->assertHasNoFormErrors();

    assertCount(1, ServiceRequestPriority::all());

    assertDatabaseHas(ServiceRequestPriority::class, $request->toArray());
});
