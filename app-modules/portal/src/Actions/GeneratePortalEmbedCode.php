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

namespace AdvisingApp\Portal\Actions;

use Exception;
use Illuminate\Support\Facades\URL;
use AdvisingApp\Portal\Enums\PortalType;

class GeneratePortalEmbedCode
{
    public function handle(PortalType $portal): string
    {
        return match ($portal) {
            PortalType::KnowledgeManagement => (function () {
                $scriptUrl = url('js/portals/knowledge-management/advising-app-knowledge-management-portal.js?');
                $portalAccessUrl = route('portals.knowledge-management.show');
                $portalDefinitionUrl = URL::signedRoute('portal.knowledge-management.define');
                $portalSearchUrl = URL::signedRoute('portal.knowledge-management.search');
                $appUrl = parse_url(config('app.url'))['host'];
                $apiUrl = route('portal.knowledge-management.define');

                return <<<EOD
                <knowledge-management-portal-embed url="{$portalDefinitionUrl}" access-url={$portalAccessUrl} search-url="{$portalSearchUrl}" app-url="{$appUrl}" api-url="{$apiUrl}"></knowledge-management-portal-embed>
                <script src="{$scriptUrl}"></script>
                EOD;
            })(),
            default => throw new Exception('Unsupported Portal.'),
        };
    }
}
