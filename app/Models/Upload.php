<?php

namespace App\Models;

use App\Models\Traits\NamedEntityTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
ini_set('upload_max_filesize', '30M');
class Upload extends Model
{
    use NamedEntityTrait;
    use HasFactory;

    public $fillable = [
        'user_id',
        'location',
        'created_at',
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
