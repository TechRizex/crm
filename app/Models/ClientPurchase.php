<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientPurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'order_no',
        'order_date',
        'items_json',
        'amount',
        'payment_status',
        'created_by',
    ];

    protected $casts = [
        'items_json' => 'array',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
