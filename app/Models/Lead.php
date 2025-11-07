<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'owner_id',
        'title',
        'source',
        'stage',
        'value_amount',
        'email',
        'phone',
        'lost_reason',
        'converted_client_id',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function convertedClient()
    {
        return $this->belongsTo(Client::class, 'converted_client_id');
    }
}
