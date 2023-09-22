<?php

namespace Assist\AssistDataModel\Filament\Resources\StudentResource\Pages;

use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Assist\Alert\Enums\AlertStatus;
use Assist\Prospect\Models\Prospect;
use Assist\Alert\Enums\AlertSeverity;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Cache;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Filters\SelectFilter;
use Assist\AssistDataModel\Models\Student;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Resources\Pages\ManageRelatedRecords;
use Assist\AssistDataModel\Filament\Resources\StudentResource;

class ManageStudentAlerts extends ManageRelatedRecords
{
    protected static string $resource = StudentResource::class;

    protected static string $relationship = 'alerts';

    // TODO: Automatically set from Filament based on relationship name
    protected static ?string $navigationLabel = 'Alerts';

    // TODO: Automatically set from Filament based on relationship name
    protected static ?string $breadcrumb = 'Alerts';

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';

    public static function getNavigationItems(array $urlParameters = []): array
    {
        $item = parent::getNavigationItems($urlParameters)[0];

        $ownerRecord = $urlParameters['record'];

        /** @var Student|Prospect $ownerRecord */
        $alertsCount = Cache::tags('alert-count')
            ->remember(
                "alert-count-{$ownerRecord->getKey()}",
                now()->addMinutes(5),
                function () use ($ownerRecord): int {
                    // TODO: When it is decided how alerts are "resolved" this will need to take that into account to only display unresolved alerts
                    return $ownerRecord->alerts()->status(AlertStatus::Active)->count();
                },
            );

        $item->badge($alertsCount > 0 ? $alertsCount : null, color: 'danger');

        return [$item];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('description'),
                TextEntry::make('severity'),
                TextEntry::make('suggested_intervention'),
                TextEntry::make('status'),
            ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Textarea::make('description')
                    ->required(),
                Select::make('severity')
                    ->options(AlertSeverity::class)
                    ->selectablePlaceholder(false),
                Textarea::make('suggested_intervention')
                    ->required(),
                Select::make('status')
                    ->options(AlertStatus::class)
                    ->selectablePlaceholder(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                TextColumn::make('description')
                    ->limit(),
                TextColumn::make('severity')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('severity')
                    ->options(AlertSeverity::class),
                SelectFilter::make('status')
                    ->options(AlertStatus::class),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                CreateAction::make(),
            ]);
    }
}
