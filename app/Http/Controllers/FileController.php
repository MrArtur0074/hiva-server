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

    public function api()
    {
        $files = File::select(['id', 'filename', 'status', 'download_link', 'created_at', 'updated_at', 'site_id'])->get(); // Получаем все записи из таблицы "files"

        return response()->json(['data' => $files], 200);
    }

    public function oneFile($id)
    {
        $file = File::select('*')->find($id);

        if (!$file) {
            return response()->json(['message' => 'File not found'], 404);
        }

        return response()->json(['data' => $file], 200);
    }
}
