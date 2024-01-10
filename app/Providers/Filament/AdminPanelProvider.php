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

namespace App\Providers\Filament;

use Filament\Panel;
use Filament\PanelProvider;
use App\Filament\Pages\Dashboard;
use Filament\Navigation\MenuItem;
use Filament\Actions\ImportAction;
use App\Filament\Pages\EditProfile;
use Filament\Tables\Columns\Column;
use Filament\Forms\Components\Field;
use App\Filament\Pages\ProductHealth;
use FilamentTiptapEditor\TiptapEditor;
use Filament\Infolists\Components\Entry;
use Filament\Navigation\NavigationGroup;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use AdvisingApp\Authorization\Filament\Pages\Auth\Login;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Saade\FilamentFullCalendar\FilamentFullCalendarPlugin;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use ShuvroRoy\FilamentSpatieLaravelHealth\FilamentSpatieLaravelHealthPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function register(): void
    {
        parent::register();

        Field::configureUsing(fn ($field) => $field->translateLabel());
        Entry::configureUsing(fn ($entry) => $entry->translateLabel());
        Column::configureUsing(fn ($column) => $column->translateLabel());
        ImportAction::configureUsing(fn (ImportAction $action) => $action->maxRows(100000));
        TiptapEditor::configureUsing(fn (TiptapEditor $editor) => $editor->gridLayouts([
            'two-columns',
            'three-columns',
            'four-columns',
            'asymmetric-left-thirds',
            'asymmetric-right-thirds',
        ]));
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('/')
            ->login(Login::class)
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->favicon(asset('/images/default-favicon.png'))
            ->readOnlyRelationManagersOnResourceViewPagesByDefault(false)
            ->maxContentWidth('full')
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->resources([])
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Artificial Intelligence'),
                NavigationGroup::make()
                    ->label('Retention CRM'),
                NavigationGroup::make()
                    ->label('Recruitment CRM'),
                NavigationGroup::make()
                    ->label('Engagement Features'),
                NavigationGroup::make()
                    ->label('Premium Features'),
                NavigationGroup::make()
                    ->label('Reporting'),
                NavigationGroup::make()
                    ->label('Users and Permissions'),
                NavigationGroup::make()
                    ->label('Product Administration'),
            ])
            ->plugins([
                FilamentSpatieLaravelHealthPlugin::make()
                    ->usingPage(ProductHealth::class),
                FilamentFullCalendarPlugin::make(),
            ])
            ->userMenuItems([
                'profile' => MenuItem::make()
                    ->url(fn () => EditProfile::getUrl()),
            ])
            ->renderHook(
                'panels::scripts.before',
                fn () => view('filament.scripts.scroll-sidebar-to-active-menu-item'),
            );
    }

    public function boot(): void {}
}