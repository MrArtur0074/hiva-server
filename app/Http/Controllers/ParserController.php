<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;

class ParserController extends Controller
{
    public function handle(Request $request)
    {
        // выполнять код, если есть POST-запрос
        if ($request->isMethod('post')) {

            // валидация формы
            $request->validate([
                'url'  => 'required|max:200|min:5'
            ]);

            return response('Parse success', 200);
        }
    }
}