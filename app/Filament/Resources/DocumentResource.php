<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentResource\Pages;
use App\Filament\Resources\DocumentResource\RelationManagers;
use App\Models\Document;
use Filament\Forms;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Livewire\Component;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('subject')
                    ->required()
                    ->minLength(2)
                    ->maxLength(255)
                    ->live(onBlur: true),
                Textarea::make('summary')
                    ->default(null)
                    ->columnSpan('full')
                    ->cols(20)
                    ->autosize()
                    ->extraInputAttributes(['class' => 'w-full'])
                    ->visible(fn (Get $get): bool => !is_null($get('summary')))
                    ->live(onBlur: true),
                Textarea::make('bullets')
                    ->default(null)
                    ->columnSpan('full')
                    ->rows(8)
                    ->cols(20)
                    ->autosize()
                    ->visible(fn(Get $get) => !is_null($get('bullets')))
                    ->live(onBlur: true),
                Textarea::make('result')
                    ->columnSpan('full')
                    ->cols(20)
                    ->autosize()
                    ->visible(fn (Get $get): bool => strlen($get('result'))),
                Actions::make([
                    self::generatePlotAction(),
                    self::generateResultAction()
                ])->columnSpan('full')

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subject')
                    ->searchable(),
                TextColumn::make('summary'),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->orderBy('id', 'DESC'))
            ->striped()
            ->deferLoading();
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocuments::route('/'),
            'create' => Pages\CreateDocument::route('/create'),
            'edit' => Pages\EditDocument::route('/{record}/edit'),
        ];
    }

    public static function generatePlotAction(): Action
    {
        return Action::make('generate_plot')
            ->label('Generate Plot')
            ->disabled(fn (Get $get): bool => $get('subject') == '')
            ->visible(fn (Get $get): bool => (is_null($get('summary')) && is_null($get('bullets'))))
            ->action(function (Component $livewire, Set $set, $state) {
                $res = $livewire->generatePlot($state);
                $set('summary', $res['summary']);
                $set('bullets', $res['bullets']);

            });
    }

    public static function generateResultAction(): Action
    {
        return Action::make('Generate Result')
            ->visible(fn (Get $get): bool => (strlen($get('summary')) && strlen($get('bullets')) && !strlen($get('result'))))
            ->action(function (Component $livewire, Set $set, $state) {
                $res = $livewire->generateResult($state);
                $set('result', $res['result']);
            });
    }
}
