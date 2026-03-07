<?php

namespace App\Filament\Resources\BookingTransactions\Tables;

use App\Models\BookingTransaction;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class BookingTransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),

                TextColumn::make('booking_trx_id')
                    ->searchable(),

                TextColumn::make('created_at'),

                // IconColumn::make('is_paid')
                //     ->boolean()
                //     ->trueColor('success')
                //     ->falseColor('danger')
                //     ->trueIcon('heroicon-o-check-circle')
                //     ->falseIcon('heroicon-o-x-circle')
                //     ->label('Terverifikasi'),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'success',
                        'danger' => 'cancelled',
                    ]),

            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('approve')
                    ->label('Approve')
                    ->action(function (BookingTransaction $record) {
                        $record->status = 'success';
                        $record->save();

                        Notification::make()
                            ->title('Order Approved')
                            ->success()
                            ->body('The order has been successfully approved.')
                            ->send();
                    })
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (BookingTransaction $record) => $record->status, ['pending', 'cancelled']),
                // Action::make('approve')
                //     ->label('Approve')
                //     ->action(function (BookingTransaction $record) {
                //         $record->is_paid = true;
                //         $record->save();

                //         Notification::make()
                //             ->title('Order Approved')
                //             ->success()
                //             ->body('The order has been successfully approved.')
                //             ->send();
                //     })
                //     ->color('success')
                //     ->requiresConfirmation()
                //     ->visible(fn (BookingTransaction $record) => ! $record->is_paid),

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
