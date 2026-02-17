<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'client_id',
        'payment_amount',
        'payment_method',
        'voucher_no',
        'status',
    ];
    public function client()
    {
        return $this->belongsTo(Clients::class, 'client_id');
    }

    public function invoicePayments()
    {
        return $this->hasMany(InvoicePayment::class);
    }
}
