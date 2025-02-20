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

namespace AdvisingApp\ServiceManagement\Filament\Resources;

use App\Models\User;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use App\Filament\Resources\UserResource;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use App\Filament\Clusters\ServiceManagementAdministration;
use AdvisingApp\ServiceManagement\Models\ChangeRequestType;
use AdvisingApp\ServiceManagement\Filament\Resources\ChangeRequestTypeResource\Pages\ListChangeRequestTypes;

class ChangeRequestTypeResource extends Resource
{
    protected static ?string $model = ChangeRequestType::class;

    protected static ?string $navigationIcon = 'heroicon-m-rectangle-stack';

    protected static ?int $navigationSort = 40;

    protected static ?string $cluster = ServiceManagementAdministration::class;

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make()
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('number_of_required_approvals')
                            ->label('Number of required approvers.'),
                        RepeatableEntry::make('userApprovers')
                            ->schema([
                                TextEntry::make('name')
                                    ->hiddenLabel()
                                    ->url(fn ($record) => UserResource::getUrl('view', ['record' => $record]))
                                    ->color('primary'),
                            ]),
                    ])
                    ->columns(1),
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->string(),
                Select::make('number_of_required_approvals')
                    ->options([
                        '0' => '0',
                        '1' => '1',
                        '2' => '2',
                    ])
                    ->required(),
                Select::make('userApprovers')
                    ->label('User approvers')
                    ->relationship('userApprovers', 'name')
                    ->preload()
                    ->multiple()
                    ->exists((new User())->getTable(), 'id'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListChangeRequestTypes::route('/'),
        ];
    }
}
