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

namespace AdvisingApp\Engagement\Actions;

use Twilio\Rest\Client;
use Twilio\Exceptions\TwilioException;
use AdvisingApp\IntegrationTwilio\Settings\TwilioSettings;

class EngagementSmsChannelDelivery extends QueuedEngagementDelivery
{
    public function deliver(): void
    {
        $settings = app(TwilioSettings::class);

        $client = new Client($settings->account_sid, $settings->auth_token);

        $messageContent = [
            'from' => $settings->from_number,
            'body' => $this->deliverable->engagement->getBody(),
        ];

        if (! app()->environment('local')) {
            $messageContent['statusCallback'] = route('inbound.webhook.twilio', ['event' => 'status_callback']);
        }

        try {
            $message = $client->messages->create(
                ! is_null(config('services.twilio.test_to_number')) ? config('services.twilio.test_to_number') : $this->deliverable->engagement->recipient->mobile,
                $messageContent
            );

            $this->deliverable->update([
                'external_reference_id' => $message->sid,
                'external_status' => $message->status,
            ]);
        } catch (TwilioException $e) {
            // TODO Notify someone of the failure
            $this->deliverable->update([
                'external_status' => $e->getMessage(),
            ]);
        }
    }
}
