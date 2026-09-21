<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageContact extends Model
{
    protected $table = 'messages_contact';

    protected $fillable = ['nom', 'email', 'telephone', 'sujet', 'message', 'lu'];

    protected $casts = ['lu' => 'boolean'];
}
