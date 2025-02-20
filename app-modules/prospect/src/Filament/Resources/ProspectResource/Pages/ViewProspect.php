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

use Filament\Actions\EditAction;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use AdvisingApp\Prospect\Models\Prospect;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use AdvisingApp\Prospect\Filament\Resources\ProspectResource;
use AdvisingApp\Notification\Filament\Actions\SubscribeHeaderAction;

class ViewProspect extends ViewRecord
{
    protected static string $resource = ProspectResource::class;

    // TODO: Automatically set from Filament
    protected static ?string $navigationLabel = 'View';

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make()
                    ->schema([
                        TextEntry::make('status.name')
                            ->label('Status')
                            ->translateLabel(),
                        TextEntry::make('source.name')
                            ->label('Source')
                            ->translateLabel(),
                        TextEntry::make('first_name')
                            ->label('First Name')
                            ->translateLabel(),
                        TextEntry::make('last_name')
                            ->label('Last Name')
                            ->translateLabel(),
                        TextEntry::make(Prospect::displayNameKey())
                            ->label('Full Name')
                            ->translateLabel(),
                        TextEntry::make('preferred')
                            ->label('Preferred Name')
                            ->translateLabel(),
                        TextEntry::make('description')
                            ->label('Description')
                            ->translateLabel(),
                        TextEntry::make('email')
                            ->label('Email')
                            ->translateLabel(),
                        TextEntry::make('email_2')
                            ->label('Email 2')
                            ->translateLabel(),
                        TextEntry::make('mobile')
                            ->label('Mobile')
                            ->translateLabel(),
                        TextEntry::make('sms_opt_out')
                            ->label('SMS Opt Out')
                            ->translateLabel()
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Yes' : 'No'),
                        TextEntry::make('email_bounce')
                            ->label('Email Bounce')
                            ->translateLabel()
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Yes' : 'No'),
                        TextEntry::make('phone')
                            ->label('Phone')
                            ->translateLabel(),
                        TextEntry::make('address')
                            ->label('Address')
                            ->translateLabel(),
                        TextEntry::make('address_2')
                            ->label('Address 2')
                            ->translateLabel(),
                        TextEntry::make('birthdate')
                            ->label('Birthdate')
                            ->translateLabel(),
                        TextEntry::make('hsgrad')
                            ->label('High School Grad')
                            ->translateLabel(),
                        TextEntry::make('assignedTo.name')
                            ->label('Assigned To')
                            ->translateLabel(),
                        TextEntry::make('createdBy.name')
                            ->label('Created By')
                            ->translateLabel(),
                    ])
                    ->columns(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            SubscribeHeaderAction::make(),
        ];
    }
}
