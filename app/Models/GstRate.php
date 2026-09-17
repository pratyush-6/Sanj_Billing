<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GstRate extends Model
{
    use HasFactory;

    protected $fillable = ['company_id', 'name', 'rate', 'status'];

    protected $casts = [
        'rate' => 'decimal:2',
    ];
}
