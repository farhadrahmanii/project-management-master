<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Models\Sprint;
use App\Models\Ticket;
use Carbon\Carbon;
use Closure;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class SprintsRelationManager extends RelationManager
{
    protected static string $relationship = 'sprints';
    protected static ?string $recordTitleAttribute = 'name';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return true; // Adjust logic as needed
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make()
                    ->columns(1)
                    ->visible(fn($record) => !$record)
                    ->extraAttributes(['class' => 'text-danger-500 text-xs'])
                    ->schema([
                        Forms\Components\Placeholder::make('information')
                            ->disableLabel()
                            ->content(new HtmlString(
                                '<span class="font-medium">' . __('Important:') . '</span> ' .
                                __('The creation of a new Sprint will create a linked Epic into the Road Map')
                            ))
                    ]),

                Forms\Components\Grid::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('Sprint name'))
                            ->maxLength(255)
                            ->columnSpan(2)
                            ->required(),

                        Forms\Components\DatePicker::make('starts_at')
                            ->label(__('Sprint start date'))
                            ->reactive()
                            ->afterStateUpdated(fn($state, Closure $set) => $set('ends_at', Carbon::parse($state)->addWeek()->subDay()))
                            ->beforeOrEqual(fn(Closure $get) => $get('ends_at'))
                            ->required(),

                        Forms\Components\DatePicker::make('ends_at')
                            ->label(__('Sprint end date'))
                            ->reactive()
                            ->afterOrEqual(fn(Closure $get) => $get('starts_at'))
                            ->required(),

                        Forms\Components\RichEditor::make('description')
                            ->label(__('Sprint description'))
                            ->columnSpan(2),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Sprint name'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('starts_at')
                    ->label(__('Sprint start date'))
                    ->date()
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('ends_at')
                    ->label(__('Sprint end date'))
                    ->date()
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('started_at')
                    ->label(__('Sprint started at'))
                    ->dateTime()
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('ended_at')
                    ->label(__('Sprint ended at'))
                    ->dateTime()
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('remaining')
                    ->label(__('Remaining'))
                    ->suffix(fn($record) => $record->remaining ? (' ' . __('days')) : '')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TagsColumn::make('tickets.name')
                    ->label(__('Tickets'))
                    ->searchable()
                    ->sortable()
                    ->limit()
            ])
            ->filters([/* Add filters if necessary */])
            ->headerActions([Tables\Actions\CreateAction::make()])
            ->actions([
                $this->startSprintAction(),
                $this->stopSprintAction(),
                $this->ticketsAction(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()])
            ->defaultSort('id');
    }

    protected function startSprintAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('start')
            ->label(__('Start sprint'))
            ->visible(fn($record) => !$record->started_at && !$record->ended_at)
            ->requiresConfirmation()
            ->color('success')
            ->button()
            ->icon('heroicon-o-play')
            ->action(function ($record) {
                $now = now();
                Sprint::where('project_id', $record->project_id)
                    ->where('id', '<>', $record->id)
                    ->whereNotNull('started_at')
                    ->whereNull('ended_at')
                    ->update(['ended_at' => $now]);

                $record->started_at = $now;
                $record->save();

                Notification::make('sprint_started')
                    ->success()
                    ->body(__('Sprint started at') . ' ' . $now)
                    ->actions([
                        Action::make('board')
                            ->color('secondary')
                            ->button()
                            ->label(fn() => $record->project->type === 'scrum' ? __('Scrum board') : __('Kanban board'))
                            ->url(fn() => $record->project->type === 'scrum' ? route('filament.pages.scrum', ['project' => $record->project->id]) : route('filament.pages.kanban', ['project' => $record->project->id])),
                    ])
                    ->send();
            });
    }

    protected function stopSprintAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('stop')
            ->label(__('Stop sprint'))
            ->visible(fn($record) => $record->started_at && !$record->ended_at)
            ->requiresConfirmation()
            ->color('danger')
            ->button()
            ->icon('heroicon-o-pause')
            ->action(function ($record) {
                $now = now();
                $record->ended_at = $now;
                $record->save();

                Notification::make('sprint_ended')
                    ->success()
                    ->body(__('Sprint ended at') . ' ' . $now)
                    ->send();
            });
    }

    protected function ticketsAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('tickets')
            ->label(__('Tickets'))
            ->color('secondary')
            ->icon('heroicon-o-ticket')
            ->mountUsing(fn(Forms\ComponentContainer $form, Sprint $record) => $form->fill([
                'tickets' => $record->tickets->pluck('id')->toArray()
            ]))
            ->modalHeading(fn($record) => $record->name . ' - ' . __('Associated tickets'))
            ->form([
                Forms\Components\Placeholder::make('info')
                    ->disableLabel()
                    ->extraAttributes(['class' => 'text-danger-500 text-xs'])
                    ->content(__('If a ticket is already associated with another sprint, it will be migrated to this sprint')),

                Forms\Components\CheckboxList::make('tickets')
                    ->label(__('Choose tickets to associate to this sprint'))
                    ->required()
                    ->extraAttributes(['class' => 'sprint-checkboxes'])
                    ->options(fn($record) => $this->getTicketOptions($record))
            ])
            ->action(fn(Sprint $record, array $data) => $this->associateTicketsToSprint($record, $data));
    }

    protected function getTicketOptions(Sprint $record): array
    {
        $results = [];
        foreach ($record->project->tickets as $ticket) {
            $results[$ticket->id] = new HtmlString(
                '<div class="w-full flex justify-between items-center">'
                . '<span>' . $ticket->name . '</span>'
                . ($ticket->sprint ? '<span class="text-xs font-medium ' . ($ticket->sprint_id == $record->id ? 'bg-gray-100 text-gray-600' : 'bg-danger-500 text-white') . ' px-2 py-1 rounded">' . $ticket->sprint->name . '</span>' : '')
                . '</div>'
            );
        }
        return $results;
    }

    protected function associateTicketsToSprint(Sprint $record, array $data): void
    {
        $tickets = $data['tickets'];
        Ticket::where('sprint_id', $record->id)->update(['sprint_id' => null]);
        Ticket::whereIn('id', $tickets)->update(['sprint_id' => $record->id]);

        Filament::notify('success', __('Tickets associated with sprint'));
    }

    public function canAttach(): bool
    {
        return false;
    }
}
