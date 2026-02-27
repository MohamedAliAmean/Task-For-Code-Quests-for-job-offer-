<?php

namespace App\Filament\Resources\CourseResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class LessonsRelationManager extends RelationManager
{
    protected static string $relationship = 'lessons';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('video_url')
                    ->label('Video URL')
                    ->required()
                    ->maxLength(2048),
                Forms\Components\Textarea::make('description')
                    ->rows(4)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('position')
                    ->numeric()
                    ->minValue(1)
                    ->helperText('Controls the ordering on the public course page. Leave empty to append to the end.'),
                Forms\Components\Toggle::make('is_preview')
                    ->label('Free preview')
                    ->default(false),
                Forms\Components\Toggle::make('is_required')
                    ->label('Required for completion')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->defaultSort('position')
            ->columns([
                Tables\Columns\TextColumn::make('position')
                    ->sortable()
                    ->label('#'),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_preview')
                    ->boolean()
                    ->label('Preview'),
                Tables\Columns\IconColumn::make('is_required')
                    ->boolean()
                    ->label('Required'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $position = isset($data['position']) ? (int) $data['position'] : 0;

                        if ($position <= 0) {
                            $max = (int) ($this->getOwnerRecord()
                                ->lessons()
                                ->withTrashed()
                                ->max('position') ?? 0);

                            $data['position'] = $max + 1;
                        }

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
