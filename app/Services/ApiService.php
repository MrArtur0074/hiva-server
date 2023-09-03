<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use App\Models\Content;
use App\Models\Site;

class ApiService
{
    private $neuralNetworkApiUrl;
    private $neuralNetworkApiKey;

    public function __construct()
    {
        $this->neuralNetworkApiUrl = 'https://api.openai.com/v1/chat/completions';
        $this->neuralNetworkApiKey = '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';
    }
    
    /*public function sendRequest($method, $path, $data = [])
    {
        $client = new Client([
            'base_uri' => $this->neuralNetworkApiUrl,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->neuralNetworkApiKey,
                'Content-Type' => 'application/json',
            ],
        ]);

        try {
            $response = $client->request($method, $path, [
                'json' => $data,
            ]);

            return $response->getBody()->getContents();
        } catch (RequestException $e) {
            // Обработка ошибок запроса, если необходимо
            return null;
        }
    }*/

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
        $index2 = '';
        $index3 = '';

        $len = 0;
        $test = [];

        foreach ($contentFromDatabase as $part) {
            // Проверка размера текущего индекса и выбор индекса для объединения
            if (str_word_count($index1) + str_word_count($part) <= 800) {
                $index1 .= $part;
                $len++;
                //array_push($test, str_word_count($index1));
            } elseif (str_word_count($index2) + str_word_count($part) <= 800) {
                $len++;
                $index2 .= $part;
            } elseif (str_word_count($index3) + str_word_count($part) <= 800) {
                $len++;
                $index3 .= $part;
            } else {
                // Если все три индекса заполнились, прекратить объединение
                break;
            }
        }

        $query = $this->uploadContentToNeuralNetwork($index1, $index2);

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

    public function uploadContentToNeuralNetwork($index1, $index2)
    {
        // Создаем HTTP-клиент Guzzle
        $client = new Client();

        $responses = [];

        foreach ([$index1, $index2] as $index => $content) {
            // Параметры для отправки в API ChatGPT
            $params = [
                "model" => "gpt-3.5-turbo",
                'messages' => [
                    // Добавляем вопросы и ответы в формате чат-бота
                    ['role' => 'system', 'content' => 'You are a helpful assistant.'],
                    ['role' => 'user', 'content' => 'во компании, как и любой здравомыслящий человек понимает, что в современном мире невозможно решить задачу путем только 1 шага или действия. Современный мир диктует нам условия, при которых необходимо выполнить несколько шагов для успешного достижения поставленной цели. Соблюдение сроков Соблюдение технологическим стандартов Эксклюзивность каждого проекта Эффективный менеджмент каждого проекта и в целом Качество получаемого продукта Безвременная гарантия каждого проекта Оперативная и эффективная техническая поддержка Для решения различных задач мы разработали несколько направлений, которые позволяют добиться необходимых результатов: Создание сайтов Создание сайтов – Разработка Web проектов для выхода компании на рынок Интернета и охват большой аудитории. Дизайн проекты Дизайн услугиРазработка Web дизайна, разработка Логотипов, разработка фирменного стиля и др. SEO продвижение SEO продвижение и Контекстная реклама, служат стимулом для роста числа потенциальных потребителей ваших услуг и не дает пасть тяжелым грузом на дно поисковых систем. Хостинг Размещение сайтов на наших серверах и приобретение доменных имен.'],
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

            // Формируем ответ в требуемом формате
            //$formattedResponse = '';

            /*foreach ($responseData['choices'] as $choice) {
                if (isset($choice['message']['role']) && $choice['message']['role'] === 'assistant') {
                    $formattedResponse .= "Q$index: {$choice['message']['content']}\n";
                    $formattedResponse .= "A$index: {$choice['message']['content']}\n\n";
                }
            }*/

            $responses[] = $responseData;
        }

        return $responses;
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