<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use App\Models\Content;
use App\Models\Site;
use Symfony\Component\Yaml\Yaml;

class ApiService
{
    private $neuralNetworkApiUrl;
    private $neuralNetworkApiKey;

    public function __construct()
    {
        $this->neuralNetworkApiUrl = 'https://api.openai.com/v1/chat/completions';
        $this->neuralNetworkApiKey = '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';
    }

    public function authorizeWithApiKey()
    {
        // Реализация авторизации в нейросети с использованием ключа
    }

    public function fetchContentFromDatabase($site)
    {
        // Получение контента из базы данных для определенного сайта
        $contentFromDatabase = $this->getContentFromDatabaseForSite($site);

        // Создание трех индексов для объединения контента
        $index1 = '';

        $len = 0;
        $test = [];

        foreach ($contentFromDatabase as $part) {
            // Проверка размера текущего индекса и выбор индекса для объединения
            if (str_word_count($index1) + str_word_count($part) <= 16000) {
                $index1 .= $part;
                $len++;
                //array_push($test, str_word_count($index1));
            } else {
                // Если все три индекса заполнились, прекратить объединение
                break;
            }
        }

        $query = $this->uploadContentToNeuralNetwork($index1);

        return [$query];
    }

    public function getContentFromDatabaseForSite($site)
    {
        // Находим сайт по его URL
        $siteModel = Site::where('url', $site)->first();

        if (!$siteModel) {
            // Сайт не найден, можно обработать этот случай по вашему усмотрению
            return [];
        }

        // Получаем все страницы для найденного сайта
        $pages = $siteModel->pages;

        $allContent = [];

        foreach ($pages as $page) {
            // Получаем контент для каждой страницы и добавляем его к общему массиву
            $content = $page->contents()->pluck('text')->toArray();
            $allContent = array_merge($allContent, $content);
        }

        return $allContent;
    }

    public function uploadContentToNeuralNetwork($index)
    {
        // Создаем HTTP-клиент Guzzle
        $client = new Client();

        $responses = '';
        $params = [
            "model" => "gpt-3.5-turbo",
            'messages' => [
                // Добавляем вопросы и ответы в формате чат-бота
                ['role' => 'system', 'content' => 'You are a helpful assistant.'],
                ['role' => 'user', 'content' => '"'.$index.'"'],
                ['role' => 'user', 'content' => 'Необходимо по тексту выше, составить вопросы и ответы по следующему шаблону: Q:(Вопрос)@A:(Ответ)'],
            ],
        ];

        // Опции для HTTP-запроса
        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->neuralNetworkApiKey,
                'Content-Type' => 'application/json', // Добавление заголовка Content-Type
            ],
            'json' => $params,
        ];

        // Отправляем запрос к API ChatGPT
        $response = $client->post($this->neuralNetworkApiUrl, $options);

        // Получаем ответ от нейросети
        $responseData = json_decode($response->getBody(), true);

        $lastMessage = '';

        // Проверяем, что ответ не пустой и не содержит ошибок
        if (isset($responseData['choices'][0]['message']['content'])) {
            $lastMessage = $responseData['choices'][0]['message']['content'];
        } else {
            $lastMessage = "Произошла ошибка при запросе к API ChatGPT";
        }

        return $lastMessage;
    }

    public function analyzeContentWithNeuralNetwork($content)
    {
        // Анализ контента нейросетью и возврат вопросов и ответов
    }

    public function saveQuestionsAndAnswersToDatabase($questions, $answers, $site)
    {
        // Сохранение вопросов и ответов в базу данных
    }
}