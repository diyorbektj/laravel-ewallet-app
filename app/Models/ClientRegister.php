<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientRegister extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'phone_number',
        'sms_code',
        'step_1',
        'step_2',
        'step_3',
        'count',
    ];
}
