<?php

/*
<COPYRIGHT>

Copyright © 2022-2023, Canyon GBS LLC

All rights reserved.

This file is part of a project developed using Laravel, which is an open-source framework for PHP.
Canyon GBS LLC acknowledges and respects the copyright of Laravel and other open-source
projects used in the development of this solution.

This project is licensed under the Affero General Public License (AGPL) 3.0.
For more details, see https://github.com/canyongbs/assistbycanyongbs/blob/main/LICENSE.

Notice:
- The copyright notice in this file and across all files and applications in this
 repository cannot be removed or altered without violating the terms of the AGPL 3.0 License.
- The software solution, including services, infrastructure, and code, is offered as a
 Software as a Service (SaaS) by Canyon GBS LLC.
- Use of this software implies agreement to the license terms and conditions as stated
 in the AGPL 3.0 License.

For more information or inquiries please visit our website at
https://www.canyongbs.com or contact us via email at legal@canyongbs.com.

</COPYRIGHT>
*/

use App\Models\User;

use function Tests\asSuperAdmin;
use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

use Assist\Prospect\Models\ProspectSource;
use Assist\Prospect\Filament\Resources\ProspectSourceResource;
use Assist\Prospect\Filament\Resources\ProspectSourceResource\Pages\ListProspectSources;

test('The correct details are displayed on the ListProspectSources page', function () {
    $prospectSources = ProspectSource::factory()
        // TODO: Fix this once Prospect factory is created
        //->has(ServiceRequest::factory()->count(fake()->randomNumber(1)), 'serviceRequests')
        ->count(10)
        ->create();

    asSuperAdmin();

    $component = livewire(ListProspectSources::class);

    $component
        ->assertSuccessful()
        ->assertCanSeeTableRecords($prospectSources)
        ->assertCountTableRecords(10)
        ->assertTableColumnExists('prospects_count');

    $prospectSources->each(
        fn (ProspectSource $prospectSource) => $component
            ->assertTableColumnStateSet(
                'id',
                $prospectSource->id,
                $prospectSource
            )
            ->assertTableColumnStateSet(
                'name',
                $prospectSource->name,
                $prospectSource
            )
        // Currently setting not test for service_requests_count as there is no easy way to check now, relying on underlying package tests
    );
});

// TODO: Sorting and Searching tests

// Permission Tests

test('ListProspectSources is gated with proper access control', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get(
            ProspectSourceResource::getUrl('index')
        )->assertForbidden();

    $user->givePermissionTo('prospect_source.view-any');

    actingAs($user)
        ->get(
            ProspectSourceResource::getUrl('index')
        )->assertSuccessful();
});
