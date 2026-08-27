<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginSetting extends Model
{
    protected $fillable = [
        'title',
        'primary_color',
        'secondary_color',
        'logo_path',
        'background_image_path',
    ];
}
