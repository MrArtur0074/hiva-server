<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class JsonController extends Controller
{
    public function getJson()
    {
        $pathToJsonFile = resource_path('terminalsPecom.json');

        if (file_exists($pathToJsonFile)) {
            $jsonContent = file_get_contents($pathToJsonFile);
            $jsonData = json_decode($jsonContent);

            return response()->json($jsonData);
        }

        return response()->json(['error' => 'JSON file not found'], 404);
    }
}
