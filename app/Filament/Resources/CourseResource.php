<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseResource\Pages;
use App\Filament\Resources\CourseResource\RelationManagers\CertificatesRelationManager;
use App\Filament\Resources\CourseResource\RelationManagers\EnrollmentsRelationManager;
use App\Filament\Resources\CourseResource\RelationManagers\LessonsRelationManager;
use App\Enums\CourseLevel;
use App\Enums\CourseStatus;
use App\Models\Course;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Filament\Forms\Set;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = 'LMS';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Set $set, ?string $state, string $operation): void {
                        if ($operation !== 'create' || blank($state)) {
                            return;
                        }

                        $set('slug', Str::slug($state));
                    }),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->helperText('Used in URL: /courses/{slug}. Must be unique (even for soft-deleted courses).'),
                Forms\Components\Select::make('level')
                    ->required()
                    ->native(false)
                    ->options(
                        collect(CourseLevel::cases())
                            ->mapWithKeys(fn (CourseLevel $level) => [
                                $level->value => Str::of($level->value)->replace('_', ' ')->title()->toString(),
                            ])
                            ->all()
                    )
                    ->afterStateHydrated(function (Forms\Components\Select $component, mixed $state): void {
                        if ($state instanceof CourseLevel) {
                            $component->state($state->value);
                        }
                    })
                    ->dehydrateStateUsing(fn (mixed $state): mixed => $state instanceof CourseLevel ? $state->value : $state),
                Forms\Components\FileUpload::make('image_path')
                    ->label('Course image')
                    ->disk('public')
                    ->directory('courses')
                    ->visibility('public')
                    ->image()
                    ->imageEditor()
                    ->maxSize(2048)
                    ->helperText('Upload an image (max 2MB). Stored on the public disk and served at /storage/*.')
                    ->nullable(),
                Forms\Components\Select::make('status')
                    ->required()
                    ->native(false)
                    ->default(CourseStatus::Draft->value)
                    ->options(
                        collect(CourseStatus::cases())
                            ->mapWithKeys(fn (CourseStatus $status) => [
                                $status->value => Str::of($status->value)->replace('_', ' ')->title()->toString(),
                            ])
                            ->all()
                    )
                    ->afterStateHydrated(function (Forms\Components\Select $component, mixed $state): void {
                        if ($state instanceof CourseStatus) {
                            $component->state($state->value);
                        }
                    })
                    ->dehydrateStateUsing(fn (mixed $state): mixed => $state instanceof CourseStatus ? $state->value : $state),
                Forms\Components\DateTimePicker::make('published_at')
                    ->seconds(false)
                    ->helperText('If empty, it will be set automatically when publishing.'),
                Forms\Components\Textarea::make('description')
                    ->rows(5)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('level')
                    ->badge()
                    ->formatStateUsing(function (mixed $state): string {
                        $value = $state instanceof CourseLevel ? $state->value : (string) $state;

                        return Str::of($value)->replace('_', ' ')->title()->toString();
                    }),
                Tables\Columns\TextColumn::make('lessons_count')
                    ->counts('lessons')
                    ->label('Lessons')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(function (mixed $state): string {
                        $value = $state instanceof CourseStatus ? $state->value : (string) $state;

                        return Str::of($value)->replace('_', ' ')->title()->toString();
                    })
                    ->color(function (mixed $state): string {
                        $value = $state instanceof CourseStatus ? $state->value : (string) $state;

                        return match ($value) {
                            CourseStatus::Draft->value => 'gray',
                            CourseStatus::Published->value => 'success',
                            default => 'gray',
                        };
                    }),
                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            LessonsRelationManager::class,
            EnrollmentsRelationManager::class,
            CertificatesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
