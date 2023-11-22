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

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;
use function PHPUnit\Framework\assertEquals;

use Assist\KnowledgeBase\Models\KnowledgeBaseCategory;
use Assist\KnowledgeBase\Filament\Resources\KnowledgeBaseCategoryResource;
use Assist\KnowledgeBase\Tests\KnowledgeBaseCategory\RequestFactories\EditKnowledgeBaseCategoryRequestFactory;

// TODO: Write EditKnowledgeBaseCategory tests
//test('A successful action on the EditKnowledgeBaseCategory page', function () {});
//
//test('EditKnowledgeBaseCategory requires valid data', function ($data, $errors) {})->with([]);

// Permission Tests

test('EditKnowledgeBaseCategory is gated with proper access control', function () {
    $user = User::factory()->create();

    $knowledgeBaseCategory = KnowledgeBaseCategory::factory()->create();

    actingAs($user)
        ->get(
            KnowledgeBaseCategoryResource::getUrl('edit', [
                'record' => $knowledgeBaseCategory,
            ])
        )->assertForbidden();

    livewire(KnowledgeBaseCategoryResource\Pages\EditKnowledgeBaseCategory::class, [
        'record' => $knowledgeBaseCategory->getRouteKey(),
    ])
        ->assertForbidden();

    $user->givePermissionTo('knowledge_base_category.view-any');
    $user->givePermissionTo('knowledge_base_category.*.update');

    actingAs($user)
        ->get(
            KnowledgeBaseCategoryResource::getUrl('edit', [
                'record' => $knowledgeBaseCategory,
            ])
        )->assertSuccessful();

    $request = collect(EditKnowledgeBaseCategoryRequestFactory::new()->create());

    livewire(KnowledgeBaseCategoryResource\Pages\EditKnowledgeBaseCategory::class, [
        'record' => $knowledgeBaseCategory->getRouteKey(),
    ])
        ->fillForm($request->toArray())
        ->call('save')
        ->assertHasNoFormErrors();

    assertEquals($request['name'], $knowledgeBaseCategory->fresh()->name);
});
