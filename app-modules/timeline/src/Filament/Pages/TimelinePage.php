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

namespace AdvisingApp\Timeline\Filament\Pages;

use Filament\Actions\ViewAction;
use Filament\Resources\Pages\Page;
use Illuminate\Database\Eloquent\Model;
use App\Actions\GetRecordFromMorphAndKey;
use AdvisingApp\Timeline\Actions\SyncTimelineData;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use AdvisingApp\Timeline\Filament\Pages\Concerns\LoadsTimelineRecords;

abstract class TimelinePage extends Page
{
    use InteractsWithRecord;
    use LoadsTimelineRecords;

    protected static ?string $navigationIcon = 'heroicon-o-queue-list';

    protected static string $view = 'timeline::page';

    public string $emptyStateMessage = 'There are no records to show on this timeline.';

    public string $noMoreRecordsMessage = 'You have reached the end of this timeline.';

    public array $modelsToTimeline = [];

    public Model $currentRecordToView;

    public Model $recordModel;

    public function mount($record): void
    {
        $this->recordModel = $this->record = $this->resolveRecord($record);

        $this->timelineRecords = collect();

        resolve(SyncTimelineData::class)->now($this->recordModel, $this->modelsToTimeline);

        $this->loadTimelineRecords();
    }

    public function viewRecord($key, $morphReference)
    {
        $this->currentRecordToView = resolve(GetRecordFromMorphAndKey::class)->via($morphReference, $key);

        $this->mountAction('view');
    }

    public function viewAction(): ViewAction
    {
        return $this->currentRecordToView
            ->timeline()
            ->modalViewAction($this->currentRecordToView);
    }

    public static function canAccess(array $parameters = []): bool
    {
        if (auth()->user()->cannot('timeline.access')) {
            return false;
        }

        if (! parent::canAccess($parameters)) {
            return false;
        }

        $record = $parameters['record'] ?? null;

        if (! $record) {
            return false;
        }

        return static::getResource()::canView($record);
    }
}
