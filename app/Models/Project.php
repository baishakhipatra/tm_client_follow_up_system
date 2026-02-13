<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'project_name',
        'client_id',
        'project_code',
        'gst_amount',
        'total_cost',
        'is_taxable',
        'payment_received',
        'project_cost',
        'invoiced_amount',
        'payment_terms',
        'start_date',
        'end_date',
        'status',
    ];

    public function client()
    {
        return $this->belongsTo(Clients::class);
    }

    public function invoices(){
        return $this->hasMany(Invoice::class);
    }
}
