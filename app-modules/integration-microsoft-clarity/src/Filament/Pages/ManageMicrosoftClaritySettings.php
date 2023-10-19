<?php

namespace Assist\IntegrationMicrosoftClarity\Filament\Pages;

use App\Models\User;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Assist\IntegrationMicrosoftClarity\Settings\MicrosoftClaritySettings;

class ManageMicrosoftClaritySettings extends SettingsPage
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $settings = MicrosoftClaritySettings::class;

    protected static ?string $title = 'Microsoft Clarity Settings';

    protected static ?string $navigationLabel = 'Microsoft Clarity';

    protected static ?string $navigationGroup = 'Product Administration';

    protected static ?int $navigationSort = 7;

    public static function shouldRegisterNavigation(): bool
    {
        /** @var User $user */
        $user = auth()->user();

        return $user->can('integration-microsoft-clarity.view_microsoft_clarity_settings');
    }

    public function mount(): void
    {
        $this->authorize('integration-microsoft-clarity.view_microsoft_clarity_settings');

        parent::mount();
    }

    public function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Toggle::make('is_enabled')
                    ->label('Enabled')
                    ->live(),
                TextInput::make('id')
                    ->visible(fn (Get $get) => $get('is_enabled')),
            ]);
    }
}
