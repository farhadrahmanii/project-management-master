<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Models\TicketStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class StatusesRelationManager extends RelationManager
{
    protected static string $relationship = 'statuses';

    protected static ?string $recordTitleAttribute = 'name';

    // Fix the signature by adding the $pageClass parameter
    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        // Adjust logic if needed
        return $ownerRecord->status_type === 'custom';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('Status name'))
                    ->required()
                    ->maxLength(255),

                Forms\Components\ColorPicker::make('color')
                    ->label(__('Status color'))
                    ->required(),

                Forms\Components\Checkbox::make('is_default')
                    ->label(__('Default status')),

                Forms\Components\TextInput::make('order')
                    ->label(__('Status order'))
                    ->integer()
                    ->default(
                        fn($livewire) =>
                        TicketStatus::where('project_id', $livewire->ownerRecord->id)->count() + 1
                    )
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order')
                    ->label(__('Status order'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\ColorColumn::make('color')
                    ->label(__('Status color'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('Status name'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_default')
                    ->label(__('Default status'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Created at'))
                    ->dateTime()
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                // Add filters if needed
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('order');
    }

    protected function canAttach(): bool
    {
        return false;
    }
}
