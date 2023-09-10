<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\File;

class FileController extends Controller
{
    public function index()
    {
        // Получите список файлов и отсортируйте их по дате создания (по убыванию)
        $files = File::orderBy('created_at', 'desc')->get();

        return view('files', compact('files'));
    }
}
