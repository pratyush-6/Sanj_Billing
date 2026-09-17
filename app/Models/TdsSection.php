<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TdsSection extends Model
{
    use HasFactory;

    protected $fillable = ['company_id', 'section', 'description', 'rate', 'status'];

    protected $casts = [
        'rate' => 'decimal:2',
    ];
}
