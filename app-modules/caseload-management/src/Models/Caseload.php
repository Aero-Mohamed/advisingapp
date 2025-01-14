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

namespace AdvisingApp\CaseloadManagement\Models;

use App\Models\User;
use App\Models\BaseModel;
use App\Models\Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use AdvisingApp\CaseloadManagement\Enums\CaseloadType;
use AdvisingApp\CaseloadManagement\Enums\CaseloadModel;
use AdvisingApp\CaseloadManagement\Actions\TranslateCaseloadFilters;

/**
 * @mixin IdeHelperCaseload
 */
class Caseload extends BaseModel
{
    protected $fillable = [
        'query',
        'filters',
        'name',
        'description',
        'model',
        'type',
    ];

    protected $casts = [
        'filters' => 'array',
        'model' => CaseloadModel::class,
        'type' => CaseloadType::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(CaseloadSubject::class);
    }

    public function scopeModel(Builder $query, CaseloadModel $model): void
    {
        $query->where('model', $model);
    }

    public function retrieveRecords(): Collection
    {
        if (count($this->subjects) > 0) {
            return $this->subjects->map(function (CaseloadSubject $subject) {
                return $subject->subject;
            });
        }

        /** @var Builder $modelQueryBuilder */
        $modelQueryBuilder = $this->model->query();

        $class = $this->model->class();

        return $modelQueryBuilder
            ->whereKey(
                resolve(TranslateCaseloadFilters::class)
                    ->handle($this)
                    ->pluck(resolve($class)->getKeyName()),
            )
            ->get();
    }

    protected static function booted(): void
    {
        static::addGlobalScope('licensed', function (Builder $builder) {
            if (! auth()->check()) {
                return;
            }

            /** @var Authenticatable $user */
            $user = auth()->user();

            foreach (CaseloadModel::cases() as $model) {
                if (! $user->hasLicense($model->class()::getLicenseType())) {
                    $builder->where('model', '!=', $model);
                }
            }
        });
    }
}
