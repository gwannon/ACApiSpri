<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    public $attributes = [
        'campaign_id' => '',
        'title' => '',
        'titleab' => '',
        'text' => '',
        'textab' => '',
        'image' => '',
    ];
}
