<?php

namespace App\Filament\Resources\HomeServices\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;

class HomeServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Fieldset::make('Details')
                    ->schema([
                        // ...
                        TextInput::make('name')
                            ->maxLength(255)
                            ->required(),

                        FileUpload::make('thumbnail')
                            ->required()
                            ->image(),

                        TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->prefix('IDR'),

                        TextInput::make('duration')
                            ->required()
                            ->numeric()
                            ->prefix('Hours'),
                    ]),

                Fieldset::make('Additional')
                    ->schema([
                        // ...
                        Repeater::make('benefits')
                            ->relationship('benefits')
                            ->schema([
                                TextInput::make('name')
                                    ->required(),
                            ]),

                        Textarea::make('about')
                            ->required(),

                        Select::make('is_popular')
                            ->options([
                                true => 'Popular',
                                false => 'Not Popular',
                            ])
                            ->required(),

                        Select::make('category_id')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),
            ]);

    }
}
