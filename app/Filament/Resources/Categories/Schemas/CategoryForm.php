<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                FileUpload::make('photo')
                    ->image()
                    ->required()
                    ->disk('public'),

                FileUpload::make('photo_white')
                    ->image()
                    ->required()
                    ->disk('public'),
            ]);
    }
}
