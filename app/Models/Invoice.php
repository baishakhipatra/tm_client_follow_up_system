<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'client_id',
        'project_id',
        'invoice_number',
        'net_price',
        'required_payment_amount',
        'status',
        'created_by',
        'updated_by',
    ];

    public function Client(){
        return $this->belongsTo(Clients::class, 'client_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function invoicePayments()
    {
        return $this->hasMany(InvoicePayment::class);
    }
}
