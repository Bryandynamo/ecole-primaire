<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistrationRequest extends Model
{
    use HasFactory;

    protected $table = 'registration_requests';

    protected $fillable = [
        'name', 'email', 'password', 'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    protected $hidden = [
        'password',
    ];
}
