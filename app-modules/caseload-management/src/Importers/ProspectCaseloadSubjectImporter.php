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

namespace AdvisingApp\CaseloadManagement\Importers;

use Illuminate\Support\Str;
use Filament\Actions\Imports\Importer;
use Illuminate\Database\Eloquent\Model;
use AdvisingApp\Prospect\Models\Prospect;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use AdvisingApp\CaseloadManagement\Models\CaseloadSubject;

class ProspectCaseloadSubjectImporter extends Importer
{
    protected static ?string $model = CaseloadSubject::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('subject')
                ->label('Email address')
                ->rules(['required', 'email'])
                ->relationship(
                    resolveUsing: fn (mixed $state) => Prospect::query()
                        ->where('email', $state)
                        ->orWhere('email_2', $state)
                        ->first(),
                )
                ->requiredMapping(),
        ];
    }

    public function resolveRecord(): ?Model
    {
        return new CaseloadSubject();
    }

    public function beforeCreate(): void
    {
        /** @var CaseloadSubject $record */
        $record = $this->record;

        $record->caseload()->associate($this->getOptions()['caseload_id']);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your caseload import has completed and ' . number_format($import->successful_rows) . ' ' . Str::plural('prospect', $import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . Str::plural('prospect', $failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
