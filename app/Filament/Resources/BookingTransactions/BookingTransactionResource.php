<?php

namespace App\Filament\Resources\BookingTransactions;

use App\Filament\Resources\BookingTransactions\Pages\CreateBookingTransaction;
use App\Filament\Resources\BookingTransactions\Pages\EditBookingTransaction;
use App\Filament\Resources\BookingTransactions\Pages\ListBookingTransactions;
use App\Filament\Resources\BookingTransactions\Schemas\BookingTransactionForm;
use App\Filament\Resources\BookingTransactions\Tables\BookingTransactionsTable;
use App\Models\BookingTransaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BookingTransactionResource extends Resource
{
    protected static ?string $model = BookingTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return BookingTransactionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BookingTransactionsTable::configure($table);
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
            'index' => ListBookingTransactions::route('/'),
            'create' => CreateBookingTransaction::route('/create'),
            'edit' => EditBookingTransaction::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
