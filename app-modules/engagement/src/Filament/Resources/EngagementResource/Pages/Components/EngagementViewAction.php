<?php

namespace Assist\Engagement\Filament\Pages\Components;

use Filament\Actions\ViewAction;
use Assist\Engagement\Filament\Concerns\EngagementInfolist;

class EngagementViewAction extends ViewAction
{
    use EngagementInfolist;

    protected function setUp(): void
    {
        parent::setUp();

        $this->infolist($this->engagementInfolist());
    }
}
