<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectLedger extends Model
{
    protected $table = 'project_ledgers';
    protected $fillable = [
        'project_id',
        'entry_date',
        'reference',
        'type',
        'debit',
        'credit',
        'balance',
        'description',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
