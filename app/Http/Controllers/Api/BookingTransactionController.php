<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingTransactionRequest;
use App\Http\Resources\Api\BookingTransactionApiResource;
use App\Models\BookingTransaction;
use App\Models\HomeService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingTransactionController extends Controller
{
    public function store(StoreBookingTransactionRequest $request)
    {
        DB::beginTransaction();

        try {
            Log::info($request->all());
            $validatedData = $request->validated();

            $serviceIds = $request->input('service_ids');

            if (empty($serviceIds)) {
                return response()->json(['message' => 'No services selected'], 400);
            }

            $services = HomeService::whereIn('id', $serviceIds)->get();

            if ($services->isEmpty()) {
                return response()->json(['message' => 'Invalid services'], 400);
            }

            $totalPrice = $services->sum('price');
            $tax = 0.11 * $totalPrice;
            $grandTotal = $totalPrice + $tax;

            $validatedData['schedule_at'] = Carbon::tomorrow()->toDateString();
            $validatedData['total_amount'] = $grandTotal;
            $validatedData['total_tax_amount'] = $tax;
            $validatedData['sub_total'] = $totalPrice;
            $validatedData['status'] = 'pending';
            //   $validatedData['name'] = $validatedData->name;
            //   $validatedData['phone'] = $validatedData->phone;
            //   $validatedData['email'] = $validatedData->email;

            $bookingTransaction = BookingTransaction::create($validatedData);

            foreach ($services as $service) {
                $bookingTransaction->transactionDetails()->create([
                    'home_service_id' => $service->id,
                    'price' => $service->price,
                ]);
            }

            // MIDTRANS CONFIG
            \Midtrans\Config::$serverKey = config('midtrans.serverKey');
            \Midtrans\Config::$isProduction = config('midtrans.isProduction');
            \Midtrans\Config::$isSanitized = config('midtrans.isSanitized');
            \Midtrans\Config::$is3ds = config('midtrans.is3ds');

            // MIDTRANS PARAMS
            $params = [
                'transaction_details' => [
                    'order_id' => $bookingTransaction->booking_trx_id,
                    'gross_amount' => $grandTotal,
                ],
                'customer_details' => [
                    'first_name' => $bookingTransaction->name,
                    // 'first_name' => auth()->user()->name,
                ],

            ];

            // GENERATE SNAP TOKEN
            $snapToken = \Midtrans\Snap::getSnapToken($params);

            // SAVE TOKEN
            // $bookingTransaction->snap_token = $snapToken;
            // $bookingTransaction->save();
            $bookingTransaction->update([
                'snap_token' => $snapToken,
            ]);

            DB::commit();

            return new BookingTransactionApiResource(
                $bookingTransaction->load('transactionDetails')
            );

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function booking_details(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'booking_trx_id' => 'required|string',
        ]);

        $booking = BookingTransaction::where('email', $request->input('email'))
            ->where('booking_trx_id', $request->input('booking_trx_id'))
            ->with([
                'transactionDetails.homeService',
            ])
            ->first();

        if (! $booking) {
            return response()->json(['message' => 'Booking not found'], 404);
        }

        return new BookingTransactionApiResource($booking);
    }
}
