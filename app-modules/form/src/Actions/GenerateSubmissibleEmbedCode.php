<?php

/*
<COPYRIGHT>

    Copyright © 2016-2024, Canyon GBS LLC. All rights reserved.

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

namespace AdvisingApp\Form\Actions;

use Exception;
use AdvisingApp\Form\Models\Form;
use Illuminate\Support\Facades\URL;
use AdvisingApp\Survey\Models\Survey;
use AdvisingApp\Form\Models\Submissible;
use AdvisingApp\Application\Models\Application;
use AdvisingApp\CaseManagement\Models\CaseForm;
use AdvisingApp\MeetingCenter\Models\EventRegistrationForm;

class GenerateSubmissibleEmbedCode
{
    public function handle(Submissible $submissible): string
    {
        return match ($submissible::class) {
            Form::class => (function () use ($submissible) {
                $scriptUrl = url('js/widgets/form/advising-app-form-widget.js?');
                $formDefinitionUrl = URL::to(
                    URL::signedRoute(
                        name: 'forms.define',
                        parameters: ['form' => $submissible],
                        absolute: false,
                    )
                );

                return <<<EOD
                <form-embed url="{$formDefinitionUrl}"></form-embed>
                <script src="{$scriptUrl}"></script>
                EOD;
            })(),
            Application::class => (function () use ($submissible) {
                $scriptUrl = url('js/widgets/application/advising-app-application-widget.js?');
                $applicationDefinitionUrl = URL::to(
                    URL::signedRoute(
                        name: 'applications.define',
                        parameters: ['application' => $submissible],
                        absolute: false,
                    )
                );

                return <<<EOD
                <application-embed url="{$applicationDefinitionUrl}"></application-embed>
                <script src="{$scriptUrl}"></script>
                EOD;
            })(),
            Survey::class => (function () use ($submissible) {
                $scriptUrl = url('js/widgets/survey/advising-app-survey-widget.js?');
                $surveyDefinitionUrl = URL::to(
                    URL::signedRoute(
                        name: 'surveys.define',
                        parameters: ['survey' => $submissible],
                        absolute: false,
                    )
                );

                return <<<EOD
                <survey-embed url="{$surveyDefinitionUrl}"></survey-embed>
                <script src="{$scriptUrl}"></script>
                EOD;
            })(),
            EventRegistrationForm::class => (function () use ($submissible) {
                /** @var EventRegistrationForm $submissible */
                $scriptUrl = url('js/widgets/events/advising-app-event-registration-form-widget.js?');
                $formDefinitionUrl = URL::to(
                    URL::signedRoute(
                        name: 'event-registration.define',
                        parameters: ['event' => $submissible->event],
                        absolute: false,
                    )
                );

                return <<<EOD
                <event-registration-embed url="{$formDefinitionUrl}"></event-registration-embed>
                <script src="{$scriptUrl}"></script>
                EOD;
            })(),
            CaseForm::class => (function () use ($submissible) {
                /** @var CaseForm $submissible */
                $scriptUrl = url('js/widgets/case-form/advising-app-case-form-widget.js?');
                $formDefinitionUrl = URL::to(
                    URL::signedRoute(
                        name: 'case-forms.define',
                        parameters: ['caseForm' => $submissible],
                        absolute: false,
                    )
                );

                return <<<EOD
                <case-form-embed url="{$formDefinitionUrl}"></case-form-embed>
                <script src="{$scriptUrl}"></script>
                EOD;
            })(),
            default => throw new Exception('Unsupported submissible type.'),
        };
    }
}
