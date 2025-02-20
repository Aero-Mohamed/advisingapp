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

namespace AdvisingApp\Prospect\Filament\Resources\ProspectResource\Pages;

use App\Models\User;
use Filament\Forms\Get;
use Filament\Tables\Table;
use App\Filament\Columns\IdColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use AdvisingApp\Prospect\Models\Prospect;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\BulkActionGroup;
use Illuminate\Database\Eloquent\Collection;
use Filament\Tables\Actions\DeleteBulkAction;
use AdvisingApp\Prospect\Models\ProspectSource;
use AdvisingApp\Prospect\Models\ProspectStatus;
use AdvisingApp\Prospect\Imports\ProspectImporter;
use AdvisingApp\CaseloadManagement\Models\Caseload;
use AdvisingApp\CaseloadManagement\Enums\CaseloadModel;
use AdvisingApp\Prospect\Filament\Resources\ProspectResource;
use AdvisingApp\Engagement\Filament\Actions\BulkEngagementAction;
use AdvisingApp\Notification\Filament\Actions\SubscribeBulkAction;
use AdvisingApp\CareTeam\Filament\Actions\ToggleCareTeamBulkAction;
use AdvisingApp\Notification\Filament\Actions\SubscribeTableAction;
use AdvisingApp\CaseloadManagement\Actions\TranslateCaseloadFilters;
use AdvisingApp\Engagement\Filament\Actions\Contracts\HasBulkEngagementAction;
use AdvisingApp\Engagement\Filament\Actions\Concerns\ImplementsHasBulkEngagementAction;

class ListProspects extends ListRecords implements HasBulkEngagementAction
{
    use ImplementsHasBulkEngagementAction;

    protected static string $resource = ProspectResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                IdColumn::make(),
                TextColumn::make(Prospect::displayNameKey())
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->translateLabel()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('mobile')
                    ->label('Mobile')
                    ->translateLabel()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->translateLabel()
                    ->state(function (Prospect $record) {
                        return $record->status->name;
                    })
                    ->color(function (Prospect $record) {
                        return $record->status->color->value;
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query
                            ->join('prospect_statuses', 'prospects.status_id', '=', 'prospect_statuses.id')
                            ->orderBy('prospect_statuses.name', $direction);
                    }),
                TextColumn::make('source.name')
                    ->label('Source')
                    ->translateLabel()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('g:ia - M j, Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('my_caseloads')
                    ->label('My Caseloads')
                    ->options(
                        auth()->user()->caseloads()
                            ->where('model', CaseloadModel::Prospect)
                            ->pluck('name', 'id'),
                    )
                    ->searchable()
                    ->optionsLimit(20)
                    ->query(fn (Builder $query, array $data) => $this->caseloadFilter($query, $data)),
                SelectFilter::make('all_caseloads')
                    ->label('All Caseloads')
                    ->options(
                        Caseload::all()
                            ->where('model', CaseloadModel::Prospect)
                            ->pluck('name', 'id'),
                    )
                    ->searchable()
                    ->optionsLimit(20)
                    ->query(fn (Builder $query, array $data) => $this->caseloadFilter($query, $data)),
                SelectFilter::make('status_id')
                    ->relationship('status', 'name')
                    ->multiple()
                    ->preload(),
                SelectFilter::make('source_id')
                    ->relationship('source', 'name')
                    ->multiple()
                    ->preload(),
                Filter::make('care_team')
                    ->label('Care Team')
                    ->query(
                        function (Builder $query) {
                            return $query
                                ->whereRelation('careTeam', 'user_id', '=', auth()->id())
                                ->get();
                        }
                    ),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                SubscribeTableAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    SubscribeBulkAction::make(),
                    BulkEngagementAction::make(context: 'prospects'),
                    DeleteBulkAction::make(),
                    ToggleCareTeamBulkAction::make(),
                    BulkAction::make('bulk_update')
                        ->icon('heroicon-o-pencil-square')
                        ->form([
                            Select::make('field')
                                ->options([
                                    'assigned_to_id' => 'Assigned To',
                                    'description' => 'Description',
                                    'email_bounce' => 'Email Bounce',
                                    'hsgrad' => 'High School Graduation Date',
                                    'sms_opt_out' => 'SMS Opt Out',
                                    'source_id' => 'Source',
                                    'status_id' => 'Status',
                                ])
                                ->required()
                                ->live(),
                            Select::make('assigned_to_id')
                                ->label('Assigned To')
                                ->relationship('assignedTo', 'name')
                                ->searchable()
                                ->exists(
                                    table: (new User())->getTable(),
                                    column: (new User())->getKeyName()
                                )
                                ->required()
                                ->visible(fn (Get $get) => $get('field') === 'assigned_to_id'),
                            Textarea::make('description')
                                ->string()
                                ->required()
                                ->visible(fn (Get $get) => $get('field') === 'description'),
                            Radio::make('email_bounce')
                                ->label('Email Bounce')
                                ->boolean()
                                ->required()
                                ->visible(fn (Get $get) => $get('field') === 'email_bounce'),
                            TextInput::make('hsgrad')
                                ->label('High School Graduation Date')
                                ->numeric()
                                ->minValue(1920)
                                ->maxValue(now()->addYears(25)->year)
                                ->required()
                                ->visible(fn (Get $get) => $get('field') === 'hsgrad'),
                            Radio::make('sms_opt_out')
                                ->label('SMS Opt Out')
                                ->boolean()
                                ->required()
                                ->visible(fn (Get $get) => $get('field') === 'sms_opt_out'),
                            Select::make('source_id')
                                ->label('Source')
                                ->relationship('source', 'name')
                                ->exists(
                                    table: (new ProspectSource())->getTable(),
                                    column: (new ProspectSource())->getKeyName()
                                )
                                ->required()
                                ->visible(fn (Get $get) => $get('field') === 'source_id'),
                            Select::make('status_id')
                                ->label('Status')
                                ->relationship('status', 'name')
                                ->exists(
                                    table: (new ProspectStatus())->getTable(),
                                    column: (new ProspectStatus())->getKeyName()
                                )
                                ->required()
                                ->visible(fn (Get $get) => $get('field') === 'status_id'),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $records->each(
                                fn (Prospect $prospect) => $prospect
                                    ->forceFill([$data['field'] => $data[$data['field']]])
                                    ->save()
                            );

                            Notification::make()
                                ->title($records->count() . ' ' . str('Prospect')->plural($records->count()) . ' Updated')
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }

    protected function caseloadFilter(Builder $query, array $data): void
    {
        if (blank($data['value'])) {
            return;
        }

        $query->whereKey(
            app(TranslateCaseloadFilters::class)
                ->handle($data['value'])
                ->pluck($query->getModel()->getQualifiedKeyName()),
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()
                ->importer(ProspectImporter::class)
                ->authorize('import', Prospect::class),
            CreateAction::make(),
        ];
    }
}
