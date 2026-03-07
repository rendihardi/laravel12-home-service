<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookingTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'booking_trx_id',
        'phone',
        'name',
        'email',
        'started_time',
        'schedule_at',
        'snap_token',
        'post_code',
        'city',
        'address',
        'sub_total',
        'total_amount',
        'total_tax_amount',
        'status',
    ];

    public static function generateUniqueTrxId()
    {
        $prefix = 'SHUJIA';

        do {
            $randomString = $prefix.mt_rand(1000, 9999); // SHUJIA4493
        } while (self::where('booking_trx_id', $randomString)->exists());

        return $randomString;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            $transaction->booking_trx_id = self::generateUniqueTrxId();
        });
    }

    public function transactionDetails()
    {
        return $this->hasMany(TransactionDetails::class);
    }
}
