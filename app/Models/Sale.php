<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'deal_id',
        'amount',
        'status',
        'booked_at',
    ];

    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }
}
