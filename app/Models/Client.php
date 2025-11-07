<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'name',
        'email',
        'phone',
        'address',
        'gst_no',
        'industry',
        'account_manager_id',
        'user_id',
        'status',
    ];

    public function accountManager()
    {
        return $this->belongsTo(User::class, 'account_manager_id');
    }

    public function sales()
    {
        return $this->hasMany(ClientSale::class);
    }

    public function purchases()
    {
        return $this->hasMany(ClientPurchase::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
