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

namespace AdvisingApp\Engagement\Policies;

use App\Models\Authenticatable;
use Illuminate\Auth\Access\Response;
use AdvisingApp\Engagement\Models\EngagementDeliverable;

class EngagementDeliverablePolicy
{
    public function viewAny(Authenticatable $authenticatable): Response
    {
        return $authenticatable->canOrElse(
            abilities: 'engagement_deliverable.view-any',
            denyResponse: 'You do not have permission to view engagement deliverables.'
        );
    }

    public function view(Authenticatable $authenticatable, EngagementDeliverable $deliverable): Response
    {
        return $authenticatable->canOrElse(
            abilities: ['engagement_deliverable.*.view', "engagement_deliverable.{$deliverable->id}.view"],
            denyResponse: 'You do not have permission to view this engagement deliverable.'
        );
    }

    public function create(Authenticatable $authenticatable): Response
    {
        return $authenticatable->canOrElse(
            abilities: ['engagement_deliverable.create'],
            denyResponse: 'You do not have permission to create engagement deliverables.'
        );
    }

    public function update(Authenticatable $authenticatable, EngagementDeliverable $deliverable): Response
    {
        if ($deliverable->hasBeenDelivered()) {
            return Response::deny('You do not have permission to update this engagement deliverable because it has already been sent.');
        }

        return $authenticatable->canOrElse(
            abilities: ['engagement_deliverable.*.update', "engagement_deliverable.{$deliverable->id}.update"],
            denyResponse: 'You do not have permission to update this engagement deliverable.'
        );
    }

    public function delete(Authenticatable $authenticatable, EngagementDeliverable $deliverable): Response
    {
        if ($deliverable->hasBeenDelivered()) {
            return Response::deny('You do not have permission to delete this engagement deliverable because it has already been sent.');
        }

        return $authenticatable->canOrElse(
            abilities: ['engagement_deliverable.*.delete', "engagement_deliverable.{$deliverable->id}.delete"],
            denyResponse: 'You do not have permission to delete this engagement deliverable.'
        );
    }

    public function restore(Authenticatable $authenticatable, EngagementDeliverable $deliverable): Response
    {
        return $authenticatable->canOrElse(
            abilities: ['engagement_deliverable.*.restore', "engagement_deliverable.{$deliverable->id}.restore"],
            denyResponse: 'You do not have permission to restore this engagement deliverable.'
        );
    }

    public function forceDelete(Authenticatable $authenticatable, EngagementDeliverable $deliverable): Response
    {
        if ($deliverable->hasBeenDelivered()) {
            return Response::deny('You cannot permanently delete this engagement deliverable because it has already been delivered.');
        }

        return $authenticatable->canOrElse(
            abilities: ['engagement_deliverable.*.force-delete', "engagement_deliverable.{$deliverable->id}.force-delete"],
            denyResponse: 'You do not have permission to permanently delete this engagement deliverable.'
        );
    }
}
