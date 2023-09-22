<?php

namespace Assist\AssistDataModel\Filament\Resources\StudentResource\Pages;

use Filament\Actions\Action;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Assist\AssistDataModel\Filament\Resources\StudentResource;
use Assist\Notifications\Filament\Actions\SubscribeHeaderAction;

class ViewStudent extends ViewRecord
{
    protected static string $resource = StudentResource::class;

    // TODO: Automatically set from Filament
    protected static ?string $navigationLabel = 'View';

    public function infolist(Infolist $infolist): Infolist
    {
        return parent::infolist($infolist)
            ->schema([
                Section::make()
                    ->schema([
                        TextEntry::make('first')
                            ->label('First Name'),
                        TextEntry::make('last')
                            ->label('Last Name'),
                        TextEntry::make('preferred')
                            ->label('Preferred Name')
                            ->default('N/A'),
                        TextEntry::make('otherid')
                            ->label('Other ID'),
                        TextEntry::make('email')
                            ->label('Email Address'),
                        TextEntry::make('sisid')
                            ->label('Student ID'),
                        TextEntry::make('mobile')
                            ->label('Mobile'),
                        TextEntry::make('address')
                            ->label('Address'),
                    ])
                    ->columns(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            SubscribeHeaderAction::make(),
            // TODO Clean this action up and extract it to be re-usable (extract to Timeline component)
            Action::make('timeline')
                ->label('Engagement Timeline')
                // TODO Figure out how to route to this more effectively
                ->url(fn (): string => "/students/{$this->record->getKey()}/engagement-timeline"),
        ];
    }
}
