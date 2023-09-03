<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionAnswer extends Model
{
    use HasFactory;

    protected $table = 'questions_and_answers'; // Укажите имя таблицы

    protected $fillable = [
        'site_id',
        'question',
        'answer',
    ];

    // Определите отношение к сайту
    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}
