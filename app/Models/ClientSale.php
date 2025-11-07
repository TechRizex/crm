<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'invoice_no',
        'sale_date',
        'total_amount',
        'tax_amount',
        'currency',
        'payment_mode',
        'status',
        'remarks',
        'created_by',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
