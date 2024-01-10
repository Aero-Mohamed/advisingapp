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

namespace AdvisingApp\Authorization\Enums;

use Exception;
use Mockery\MockInterface;
use SocialiteProviders\Manager\Config;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Contracts\Provider;

enum SocialiteProvider: string
{
    case Azure = 'azure';

    case AzureCalendar = 'azure_calendar';

    case Google = 'google';

    public function driver(): Provider|MockInterface
    {
        return Socialite::driver(
            match ($this->value) {
                'azure', 'azure_calendar' => 'azure',
                'google' => 'google',
                default => throw new Exception('Invalid socialite provider'),
            }
        );
    }

    public function config(): Config
    {
        return match ($this->value) {
            'azure' => new Config(
                config('services.azure.client_id'),
                config('services.azure.client_secret'),
                config('services.azure.redirect'),
                ['tenant' => config('services.azure.tenant_id', 'common')]
            ),
            'azure_calendar' => new Config(
                key: config('services.azure_calendar.client_id'),
                secret: config('services.azure_calendar.client_secret'),
                callbackUri: route('calendar.outlook.callback'),
                additionalProviderConfig: ['tenant' => config('services.azure_calendar.tenant_id', 'common')]
            ),
            'google' => new Config(
                config('services.google.client_id'),
                config('services.google.client_secret'),
                config('services.google.redirect'),
            ),
            default => throw new Exception('Invalid socialite provider'),
        };
    }
}