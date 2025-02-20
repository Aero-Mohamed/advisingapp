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

namespace AdvisingApp\ServiceManagement\Filament\Resources\ServiceRequestResource\Pages;

use Filament\Tables\Table;
use App\Filament\Columns\IdColumn;
use Filament\Actions\CreateAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use AdvisingApp\ServiceManagement\Models\ServiceRequest;
use AdvisingApp\ServiceManagement\Enums\SlaComplianceStatus;
use AdvisingApp\StudentDataModel\Models\Scopes\EducatableSort;
use AdvisingApp\ServiceManagement\Models\ServiceRequestPriority;
use AdvisingApp\StudentDataModel\Models\Scopes\EducatableSearch;
use AdvisingApp\ServiceManagement\Filament\Resources\ServiceRequestResource;

class ListServiceRequests extends ListRecords
{
    protected ?string $heading = 'Service Management';

    protected static string $resource = ServiceRequestResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with([
                'latestInboundServiceRequestUpdate',
                'latestOutboundServiceRequestUpdate',
                'priority' => [
                    'sla',
                ],
                'status',
            ]))
            ->columns([
                IdColumn::make(),
                TextColumn::make('service_request_number')
                    ->label('Service Request #')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('respondent.display_name')
                    ->label('Related To')
                    ->getStateUsing(fn (ServiceRequest $record) => $record->respondent->{$record->respondent::displayNameKey()})
                    ->searchable(query: fn (Builder $query, $search) => $query->tap(new EducatableSearch(relationship: 'respondent', search: $search)))
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->tap(new EducatableSort($direction))),
                TextColumn::make('respondent.sisid')
                    ->label('SIS ID')
                    ->searchable()
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        // Update this if any other relations are added to the ServiceRequest model respondent relationship
                        return $query->join('students', function (JoinClause $join) {
                            $join->on('service_requests.respondent_id', '=', 'students.sisid')
                                ->where('service_requests.respondent_type', '=', 'student');
                        })->orderBy('sisid', $direction);
                    }),
                TextColumn::make('respondent.otherid')
                    ->label('Other ID')
                    ->searchable()
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        // Update this if any other relations are added to the ServiceRequest model respondent relationship
                        return $query->join('students', function (JoinClause $join) {
                            $join->on('service_requests.respondent_id', '=', 'students.sisid')
                                ->where('service_requests.respondent_type', '=', 'student');
                        })->orderBy('otherid', $direction);
                    }),
                TextColumn::make('division.name')
                    ->label('Division')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('assignedTo.user.name')
                    ->label('Assigned to')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('response_sla_compliance')
                    ->label('SLA Response')
                    ->state(fn (ServiceRequest $record): ?SlaComplianceStatus => $record->getResponseSlaComplianceStatus())
                    ->tooltip(fn (ServiceRequest $record): ?string => $record->getResponseSlaComplianceStatus()?->getLabel()),
                IconColumn::make('resolution_sla_compliance')
                    ->label('SLA Resolution')
                    ->state(fn (ServiceRequest $record): ?SlaComplianceStatus => $record->getResolutionSlaComplianceStatus())
                    ->tooltip(fn (ServiceRequest $record): ?string => $record->getResolutionSlaComplianceStatus()?->getLabel()),
            ])
            ->filters([
                SelectFilter::make('priority')
                    ->relationship('priority', 'name', fn (Builder $query) => $query->with('type'))
                    ->getOptionLabelFromRecordUsing(fn (ServiceRequestPriority $record) => "{$record->type->name} - {$record->name}")
                    ->multiple()
                    ->preload(),
                SelectFilter::make('status')
                    ->relationship('status', 'name')
                    ->multiple()
                    ->preload(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('60s');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add Service Request'),
        ];
    }
}
