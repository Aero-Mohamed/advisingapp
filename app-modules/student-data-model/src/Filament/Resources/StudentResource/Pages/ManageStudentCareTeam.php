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

namespace AdvisingApp\StudentDataModel\Filament\Resources\StudentResource\Pages;

use App\Models\User;
use Filament\Tables\Table;
use App\Models\Scopes\HasLicense;
use App\Filament\Columns\IdColumn;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Resources\UserResource;
use Filament\Tables\Actions\AttachAction;
use Filament\Tables\Actions\DetachAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DetachBulkAction;
use AdvisingApp\StudentDataModel\Models\Student;
use Filament\Resources\Pages\ManageRelatedRecords;
use AdvisingApp\StudentDataModel\Filament\Resources\StudentResource;

class ManageStudentCareTeam extends ManageRelatedRecords
{
    protected static string $resource = StudentResource::class;

    protected static string $relationship = 'careTeam';

    // TODO: Automatically set from Filament based on relationship name
    protected static ?string $navigationLabel = 'Care Team';

    // TODO: Automatically set from Filament based on relationship name
    protected static ?string $breadcrumb = 'Care Team';

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    //TODO: manually override check canAccess for policy

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                IdColumn::make(),
                TextColumn::make('name')
                    ->url(fn ($record) => UserResource::getUrl('view', ['record' => $record]))
                    ->color('primary'),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Add to Care Team')
                    ->modalHeading(function () {
                        /** @var Student $student */
                        $student = $this->getOwnerRecord();

                        return "Add a User to {$student->display_name}'s Care Team";
                    })
                    ->modalSubmitActionLabel('Add')
                    ->attachAnother(false)
                    ->color('primary')
                    ->recordSelect(
                        fn (Select $select) => $select->placeholder('Select a User'),
                    )
                    ->recordSelectOptionsQuery(
                        fn (Builder $query) => $query->tap(new HasLicense(Student::getLicenseType())),
                    )
                    ->successNotificationTitle(function (User $record) {
                        /** @var Student $student */
                        $student = $this->getOwnerRecord();

                        return "{$record->name} was added to {$student->display_name}'s Care Team";
                    }),
            ])
            ->actions([
                DetachAction::make()
                    ->label('Remove')
                    ->modalHeading(function (User $record) {
                        /** @var Student $student */
                        $student = $this->getOwnerRecord();

                        return "Remove {$record->name} from {$student->display_name}'s Care Team";
                    })
                    ->modalSubmitActionLabel('Remove')
                    ->successNotificationTitle(function (User $record) {
                        /** @var Student $student */
                        $student = $this->getOwnerRecord();

                        return "{$record->name} was removed from {$student->display_name}'s Care Team";
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DetachBulkAction::make()
                        ->label('Remove selected')
                        ->modalHeading(function () {
                            /** @var Student $student */
                            $student = $this->getOwnerRecord();

                            return "Remove selected users from {$student->display_name}'s Care Team";
                        })
                        ->modalSubmitActionLabel('Remove')
                        ->successNotificationTitle(function () {
                            /** @var Student $student */
                            $student = $this->getOwnerRecord();

                            return "All selected users were removed from {$student->display_name}'s Care Team";
                        }),
                ]),
            ])
            ->emptyStateHeading('No Users')
            ->inverseRelationship('studentCareTeams');
    }
}
