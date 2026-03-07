<?php

namespace App\Filament\Resources\BookingTransactions\Schemas;

use App\Models\HomeService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;

class BookingTransactionForm
{
    public static function updateTotals(Get $get, Set $set): void
    {
        $selectedHomeServices = collect($get('transactionDetails'))->filter(fn ($item) => ! empty($item['home_service_id']));

        $prices = HomeService::find($selectedHomeServices->pluck('home_service_id'))->pluck('price', 'id');

        $subtotal = $selectedHomeServices->reduce(function ($subtotal, $item) use ($prices) {
            return $subtotal + ($prices[$item['home_service_id']] * 1);
        }, 0);

        $total_tax_amount = round($subtotal * 0.11);

        $total_amount = round($subtotal + $total_tax_amount);

        $set('total_amount', number_format($total_amount, 0, ',', '.'));

        $set('total_tax_amount', number_format($total_tax_amount, 0, ',', '.'));
        $set('sub_total', number_format($subtotal, 0, ',', '.'));
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Wizard::make([
                    Step::make('Product and Price')
                        ->completedIcon('heroicon-m-hand-thumb-up')
                        ->description('Add your product items')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    Repeater::make('transactionDetails')
                                        ->relationship('transactionDetails')
                                        ->schema([
                                            Select::make('home_service_id')
                                                ->relationship('homeService', 'name')
                                                ->searchable()
                                                ->preload()
                                                ->required()
                                                ->label('Select Product')
                                                ->live()
                                                ->afterStateUpdated(function ($state, callable $set) {
                                                    $home_service = HomeService::find($state);
                                                    $set('price', $home_service ? $home_service->price : 0);
                                                }),

                                            TextInput::make('price')
                                                ->required()
                                                ->numeric()
                                                ->readOnly()
                                                ->label('Price')
                                                ->hint('Price will be filled automatically based on product selection'),
                                        ])
                                        ->live()
                                        ->afterStateUpdated(function (Get $get, Set $set) {
                                            self::updateTotals($get, $set);
                                        })
                                        ->columnSpan('full')
                                        ->columns(1)
                                        ->label('Choose Product'),
                                ]),
                            Grid::make(3)
                                ->schema([
                                    TextInput::make('sub_total')
                                        ->numeric()
                                        ->readOnly()
                                        ->label('Sub Total Amount'),

                                    TextInput::make('total_amount')
                                        ->numeric()
                                        ->readOnly()
                                        ->label('Total Amount'),

                                    TextInput::make('total_tax_amount')
                                        ->numeric()
                                        ->readOnly()
                                        ->label('Total Tax (11%)'),
                                ]),
                        ]),

                    Step::make('Customer Information')
                        ->completedIcon('heroicon-m-hand-thumb-up')
                        ->description('For our marketing')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    TextInput::make('name')
                                        ->required()
                                        ->maxLength(255),

                                    TextInput::make('phone')
                                        ->required()
                                        ->maxLength(255),

                                    TextInput::make('email')
                                        ->required()
                                        ->maxLength(255),
                                ]),
                        ]),

                    Step::make('Delivery Information')
                        ->completedIcon('heroicon-m-hand-thumb-up')
                        ->description('Put your correct address')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    TextInput::make('city')
                                        ->required()
                                        ->maxLength(255),

                                    TextInput::make('post_code')
                                        ->required()
                                        ->maxLength(255),

                                    DatePicker::make('schedule_at')
                                        ->required(),

                                    TimePicker::make('started_time')
                                        ->required(),

                                    Textarea::make('address')
                                        ->required()
                                        ->maxLength(255),
                                ]),
                        ]),

                    Step::make('Payment Information')
                        ->description('Review your payment')
                        ->schema([
                            Grid::make(3)
                                ->schema([
                                    // TextInput::make('booking_trx_id')
                                    //     ->required(),

                                    ToggleButtons::make('status')
                                        ->options([
                                            'pending' => 'Pending',
                                            'success' => 'Success',
                                            'cancelled' => 'Cancelled',
                                        ])
                                        ->grouped()
                                        ->required(),

                                    // FileUpload::make('proof')
                                    //     ->image()
                                    //     ->required(),
                                ]),
                        ]),

                ])
                    ->columnSpan('full')
                    ->columns(1)
                    ->skippable(),
            ]);
    }
}
