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

namespace AdvisingApp\Campaign\Filament\Blocks;

use Closure;
use Carbon\CarbonImmutable;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Textarea;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use AdvisingApp\Division\Models\Division;
use Filament\Forms\Components\DateTimePicker;
use AdvisingApp\Interaction\Models\Interaction;
use AdvisingApp\Campaign\Settings\CampaignSettings;
use AdvisingApp\Interaction\Models\InteractionType;
use AdvisingApp\Interaction\Models\InteractionDriver;
use AdvisingApp\Interaction\Models\InteractionStatus;
use AdvisingApp\Interaction\Models\InteractionOutcome;
use AdvisingApp\Interaction\Models\InteractionCampaign;
use AdvisingApp\Interaction\Models\InteractionRelation;

class InteractionBlock extends CampaignActionBlock
{
    protected Model | string | Closure | null $model = Interaction::class;

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Interaction');

        $this->schema($this->createFields());
    }

    public function generateFields(string $fieldPrefix = ''): array
    {
        return [
            Fieldset::make('Details')
                ->schema([
                    Select::make($fieldPrefix . 'interaction_campaign_id')
                        ->relationship('campaign', 'name')
                        ->label('Campaign')
                        ->required()
                        ->exists((new InteractionCampaign())->getTable(), 'id'),
                    Select::make($fieldPrefix . 'interaction_driver_id')
                        ->relationship('driver', 'name')
                        ->preload()
                        ->label('Driver')
                        ->required()
                        ->exists((new InteractionDriver())->getTable(), 'id'),
                    Select::make($fieldPrefix . 'division_id')
                        ->relationship('division', 'name')
                        ->preload()
                        ->label('Division')
                        ->required()
                        ->exists((new Division())->getTable(), 'id'),
                    Select::make($fieldPrefix . 'interaction_outcome_id')
                        ->relationship('outcome', 'name')
                        ->preload()
                        ->label('Outcome')
                        ->required()
                        ->exists((new InteractionOutcome())->getTable(), 'id'),
                    Select::make($fieldPrefix . 'interaction_relation_id')
                        ->relationship('relation', 'name')
                        ->preload()
                        ->label('Relation')
                        ->required()
                        ->exists((new InteractionRelation())->getTable(), 'id'),
                    Select::make($fieldPrefix . 'interaction_status_id')
                        ->relationship('status', 'name')
                        ->preload()
                        ->label('Status')
                        ->required()
                        ->exists((new InteractionStatus())->getTable(), 'id'),
                    Select::make($fieldPrefix . 'interaction_type_id')
                        ->relationship('type', 'name')
                        ->preload()
                        ->label('Type')
                        ->required()
                        ->exists((new InteractionType())->getTable(), 'id'),
                ]),
            Fieldset::make('Time')
                ->schema([
                    DateTimePicker::make($fieldPrefix . 'start_datetime')
                        ->required(),
                    DateTimePicker::make($fieldPrefix . 'end_datetime')
                        ->required(),
                ]),
            Fieldset::make('Notes')
                ->schema([
                    TextInput::make($fieldPrefix . 'subject')
                        ->required(),
                    Textarea::make($fieldPrefix . 'description')
                        ->required(),
                ]),
            DateTimePicker::make($fieldPrefix . 'execute_at')
                ->label('When should the journey step be executed?')
                ->columnSpanFull()
                ->timezone(app(CampaignSettings::class)->getActionExecutionTimezone())
                ->helperText(app(CampaignSettings::class)->getActionExecutionTimezoneLabel())
                ->lazy()
                ->hint(fn ($state): ?string => filled($state) ? $this->generateUserTimezoneHint(CarbonImmutable::parse($state)) : null)
                ->required()
                ->minDate(now()),
        ];
    }

    public static function type(): string
    {
        return 'interaction';
    }
}
