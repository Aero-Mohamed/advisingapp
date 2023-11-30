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

namespace Assist\Engagement\Models;

use Exception;
use App\Models\User;
use App\Models\BaseModel;
use Assist\Campaign\Models\CampaignAction;
use Assist\Engagement\Actions\CreateEngagementBatch;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Assist\Engagement\Models\Concerns\HasManyEngagements;
use Assist\Campaign\Models\Contracts\ExecutableFromACampaignAction;
use Assist\Engagement\DataTransferObjects\EngagementBatchCreationData;

/**
 * @mixin IdeHelperEngagementBatch
 */
class EngagementBatch extends BaseModel implements ExecutableFromACampaignAction
{
    use HasManyEngagements;

    protected $fillable = [
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function executeFromCampaignAction(CampaignAction $action): bool|string
    {
        try {
            CreateEngagementBatch::dispatch(EngagementBatchCreationData::from([
                'user' => $action->campaign->user,
                'records' => $action->campaign->caseload->retrieveRecords(),
                'deliveryMethod' => $action->data['delivery_method'],
                'subject' => $action->data['subject'] ?? null,
                'body' => $action->data['body'] ?? null,
                'bodyJson' => $action->data['bodyJson'] ?? null,
            ]));

            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }

        // Do we need to be able to relate campaigns/actions to the RESULT of their actions?
    }
}
