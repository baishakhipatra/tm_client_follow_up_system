<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'client_id',
        'project_id',
        'invoice_type',
        'invoice_number',
        'invoice_date',
        'due_date',
        'amount',
        'paid_amount',
        'pending_amount',
        'payment_date',
        'payment_method',
        'payment_notes',
        'status',
    ];

    public function Client(){
        return $this->belongsTo(Clients::class, 'client_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

}
