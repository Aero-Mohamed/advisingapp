<?php

/*
<COPYRIGHT>

Copyright © 2022-2023, Canyon GBS LLC

All rights reserved.

This file is part of a project developed using Laravel, which is an open-source framework for PHP.
Canyon GBS LLC acknowledges and respects the copyright of Laravel and other open-source
projects used in the development of this solution.

This project is licensed under the Affero General Public License (AGPL) 3.0.
For more details, see https://github.com/canyongbs/assistbycanyongbs/blob/main/LICENSE.

Notice:
- The copyright notice in this file and across all files and applications in this
 repository cannot be removed or altered without violating the terms of the AGPL 3.0 License.
- The software solution, including services, infrastructure, and code, is offered as a
 Software as a Service (SaaS) by Canyon GBS LLC.
- Use of this software implies agreement to the license terms and conditions as stated
 in the AGPL 3.0 License.

For more information or inquiries please visit our website at
https://www.canyongbs.com or contact us via email at legal@canyongbs.com.

</COPYRIGHT>
*/

namespace Assist\MeetingCenter\Filament\Widgets;

use App\Models\User;
use Livewire\Attributes\On;
use Assist\MeetingCenter\Models\CalendarEvent;
use Saade\FilamentFullCalendar\Data\EventData;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use Assist\MeetingCenter\Filament\Resources\CalendarEventResource;

class CalendarWidget extends FullCalendarWidget
{
    public function fetchEvents(array $info): array
    {
        /** @var User $user */
        $user = auth()->user();

        return $user->calendar
            ->events()
            ->get()
            ->map(fn (CalendarEvent $event) => EventData::make()
                ->id($event->id)
                ->title($event->title)
                ->start($event->starts_at)
                ->end($event->ends_at)
                ->url(CalendarEventResource::getUrl('view', ['record' => $event]), true)
                ->extendedProps(['shouldOpenInNewTab' => true]))
            ->toArray();
    }

    #[On('refresh-events')]
    public function refreshEvents(): void
    {
        $this->refreshRecords();
    }
}
