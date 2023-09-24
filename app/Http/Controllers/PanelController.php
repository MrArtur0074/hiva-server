<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Site;
use App\Models\Page;
use App\Models\Content;
use App\Models\File;
use App\Services\ApiService;
use Symfony\Component\Yaml\Yaml;

class PanelController extends Controller
{
    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function index()
    {
        $sites = Site::all();
        return view('panel', compact('sites'));
    }

    // Метод для загрузки страниц сайта
    public function loadPages($siteId)
    {
        $pages = Page::where('site_id', $siteId)->get();
        return response()->json(['pages' => $pages]);
    }

    public function generateFile(Request $request) {
        $selectedValues = $request->input('selectedValues');
        $language = $request->input('languageSelect', 'ru');

        $contentArray = [];

        foreach ($selectedValues as $pageId) {
            $page = Page::find($pageId);
            if ($page) {
                $content = Content::where('page_id', $page->id)->first();
                if ($content) {
                    $contentArray[] = $content->text;
                }
            }
        }

        // Выполните запрос, чтобы получить id и название сайта
        $siteData = Site::select('sites.id', 'sites.url')
        ->join('pages', 'sites.id', '=', 'pages.site_id')
        ->whereIn('pages.id', $selectedValues)
        ->first();

        $siteId = null;
        $siteName = null;

        if ($siteData) {
            $siteId = $siteData->id;
            $siteName = $siteData->url;
        }

        if (filter_var($siteName, FILTER_VALIDATE_URL)) {
            $parsedUrl = parse_url($siteName);
            // Извлекаем только доменное имя
            $siteName = $parsedUrl['host'];
            $siteName = str_replace(".", "", $siteName);
        } else {
            $siteName = str_replace(".xml", "", $siteName);
        }

        // Функция для разделения строки на предложения
        function splitIntoSentences($text) {
            // Разделение по точке и пробелу, предполагая, что предложения разделяются точкой и пробелом
            return preg_split('/\. /', $text);
        }

        // Создаем массив для уникальных предложений
        $uniqueSentences = [];

        // Итерируемся по каждой строке
        foreach ($contentArray as $content) {
            // Разделяем строку на предложения
            $sentences = splitIntoSentences($content);

            // Итерируемся по каждому предложению
            foreach ($sentences as $sentence) {
                // Если это предложение еще не было добавлено в уникальные предложения, добавляем его
                if (!in_array($sentence, $uniqueSentences)) {
                    $uniqueSentences[] = $sentence;
                }
            }
        }

        // Объедините контент в одну переменную
        $combinedContent = implode(". ", $uniqueSentences);

        // Генерируем уникальное имя файла
        $filename = $siteName . uniqid() . '.yaml';

        // Создаем новую запись в таблице files с начальным статусом "в создании"
        $newFile = new File;
        $newFile->filename = $filename;
        $newFile->status = 'Создается';
        $newFile->site_id = $siteId;
        $pathToFile = "/"."download/".$filename;
        $newFile->download_link = $pathToFile;
        $newFile->save();

        $response = $this->apiService->uploadContentToNeuralNetwork($combinedContent, $language);

        if (isset($response) && !empty($response)) {
            //$responses[] = $responseData;
            // Формируем структуру данных для YAML
            $data = [
                'category' => 'english_demo',
                'conversations' => [],
            ];

            // Формируем структуру данных для второго YAML
            $category = $data['category'];
            $conversationLines = [];

            // Разделяем текст на вопросы и ответы
            $qaPairs = preg_split('/(Q:|A:)/', $response, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

            print_r($qaPairs);

            for ($i = 0; $i < count($qaPairs); $i += 1) {
                if ($qaPairs[$i] == "Q:") continue;
                if ($qaPairs[$i] == "A:") continue;
                if ($qaPairs[$i-1] == "Q:") {
                    if (strpos($qaPairs[$i], "Q1:") !== false || strpos($qaPairs[$i], "A1:") !== false)
                        continue;
                    $question = trim($qaPairs[$i]);
                    $answer = trim($qaPairs[$i + 2]);
                    $data['conversations'][] = [$question, $answer];

                    // Формируем строку для вопроса и ответа
                    $conversationLines[] = "- - $question\n  - $answer";
                }
            }

            // Формируем итоговую строку
            $output = "category:\n- $category\nconversations:\n" . implode("\n", $conversationLines);

            // Преобразуем данные в YAML
            $yaml = Yaml::dump($data);

            // Путь к директории /resources
            $resourcesPath = base_path('storage/app/public/files/');

            // Полный путь к файлу
            $filePath = $resourcesPath . $filename;

            // Проверяем существование директории и создаем ее, если она не существует
            if (!is_dir($resourcesPath)) {
                mkdir($resourcesPath, 0777, true);
            }

            // Открываем файл для записи
            $file = fopen($filePath, 'w');

            if ($file) {
                fwrite($file, $output);
                fclose($file);
            }

            // Преобразуем YAML в UTF-8, если он не в этой кодировке
            /*if (mb_detect_encoding($yaml, 'UTF-8', true) !== 'UTF-8') {
                $yaml = mb_convert_encoding($yaml, 'UTF-8', 'auto');
            }*/

            // Записываем данные в файл
            //file_put_contents($fileName, $output);

            // Сохраняем YAML-файл
            //file_put_contents($filePath, $yaml);

            $newFile->status = 'Завершен';
            $newFile->save();
        }

        return response()->json(['message' => 'Добавлено в очередь']);
    }
}
