<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketPriorityResource\Pages;
use App\Models\TicketPriority;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class TicketPriorityResource extends Resource
{
    protected static ?string $model = TicketPriority::class;

    protected static ?string $navigationIcon = 'heroicon-o-badge-check';

    protected static ?int $navigationSort = 4;

    public static function getNavigationLabel(): string
    {
        return __('Ticket priorities');
    }

    public static function getPluralLabel(): ?string
    {
        return static::getNavigationLabel();
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Referential');
    }

    // Make form static
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label(__('Priority name'))
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\ColorPicker::make('color')
                                    ->label(__('Priority color'))
                                    ->required(),

                                Forms\Components\Checkbox::make('is_default')
                                    ->label(__('Default priority'))
                                    ->helperText(
                                        __('If checked, this priority will be automatically affected to new tickets')
                                    ),
                            ])
                    ])
            ]);
    }

    // Make getPages static
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTicketPriorities::route('/'),
            'create' => Pages\CreateTicketPriority::route('/create'),
            'view' => Pages\ViewTicketPriority::route('/{record}'),
            'edit' => Pages\EditTicketPriority::route('/{record}/edit'),
        ];
    }

    // Make getRelations static if needed
    public static function getRelations(): array
    {
        return [
            // Add relations if needed
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ColorColumn::make('color')
                    ->label(__('Priority color'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('Priority name'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_default')
                    ->label(__('Default priority'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Created at'))
                    ->dateTime()
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([/* Add filters if needed */])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }
}
