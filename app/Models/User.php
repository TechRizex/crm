<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'uuid',
        'name',
        'email',
        'phone',
        'password',
        'role_id',
        'department_id',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function clients()
    {
        return $this->hasMany(Client::class, 'account_manager_id');
    }

    public function tasksAssigned()
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function tasksCreated()
    {
        return $this->hasMany(Task::class, 'assigned_by');
    }

    public function sales()
    {
        return $this->hasMany(ClientSale::class, 'created_by');
    }
}
