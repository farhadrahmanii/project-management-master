<?php

namespace App\Filament\Resources\TicketResource\Pages;

use Filament\Pages\Actions;
use App\Filament\Resources\TicketResource;
use Filament\Resources\Pages\CreateRecord;
use Heloufir\FilamentWorkflowManager\Core\WorkflowResource;

class CreateTicket extends CreateRecord
{
    use WorkflowResource;
    protected static string $resource = TicketResource::class;
}
