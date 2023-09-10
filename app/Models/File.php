<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename', 'status', 'download_link', 'site_id'
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}
