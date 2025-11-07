<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deal extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'account_id',
        'title',
        'amount',
        'probability',
        'expected_close_date',
        'lost_reason',
        'created_by',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'account_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sale()
    {
        return $this->hasOne(Sale::class);
    }
}
