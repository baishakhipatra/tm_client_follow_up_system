<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Clients extends Model
{
    protected $table = 'clients';

    protected $fillable = [
        'client_name',
        'company_name',
        'primary_email',
        'secondary_email',
        'phone_number',
        'billing_address',
        'gst',
        'status',
    ];
}
