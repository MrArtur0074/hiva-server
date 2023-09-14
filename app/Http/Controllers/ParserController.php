<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Http\Response;
use Symfony\Component\Process\Process;
use App\Models\Site;
use App\Models\Page;
use App\Models\Content;
use App\Services\ApiService;

class ParserController extends Controller
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function handle(Request $request)
    {
        // выполнять код, если есть POST-запрос
        if ($request->isMethod('post')) {
            $sitemapContent = '';

            // Проверяем, был ли загружен файл
            if ($request->hasFile('uploadedFile')) {
                $file = $request->file('uploadedFile');
                $fileName = $file->getClientOriginalName();
                // Получаем содержимое файла
                $sitemapContent = file_get_contents($file->getRealPath());

                // Проверьте, существует ли сайт с таким URL
                $site = Site::where('url', $fileName)->first();

                if (!$site) {
                    // Если сайта с таким URL нет, создайте новую запись
                    $site = new Site();
                    $site->url = $fileName;
                    $site->save();
                }
                
            } else {
                // валидация формы
                $request->validate([
                    'url'  => 'required|max:200|min:5'
                ]);

                $sitemapUrl = $request->input('url');

                // Проверьте, существует ли сайт с таким URL
                $site = Site::where('url', $sitemapUrl)->first();

                if (!$site) {
                    // Если сайта с таким URL нет, создайте новую запись
                    $site = new Site();
                    $site->url = $sitemapUrl;
                    $site->save();
                }

                $sitemapContent = file_get_contents($sitemapUrl);
            }

            $pattern = '/(?:<loc>)?(https?:\/\/[^<>\s]+)(?:<\/loc>)?/i';

            // Извлеките все URL из sitemap
            $links = [];

            // Страницы из которых еще не извлекали контент
            $content_links = [];

            if (preg_match_all($pattern, $sitemapContent, $matches)) {
                $links = $matches[0]; // Используйте $matches[0] для ссылок
            } else {
                $links = [];
            }

            // Фильтрация ссылок
            $filtered_links = [];

            // $urlParts = parse_url($sitemapUrl);
            // $domain = isset($urlParts['host']) ? $urlParts['host'] : '';

            foreach ($links as $link) {
                // Проверяем, что ссылка относится к данному сайту (замените example.com на ваш домен)
                // if (strpos($link, $domain) !== false) {
                    // Проверяем, что ссылка не является ссылкой на изображение или медиафайл
                    if (!preg_match('/\.(jpg|jpeg|png|gif|mp3|mp4|pdf)$/i', $link)) {
                        $filtered_links[] = $link;
                    }
                // }
            }

            // Перебор массива ссылок
            foreach ($filtered_links as &$link) {
                // Удаление HTML-тегов из ссылки
                $link = strip_tags($link);

                // Проверьте, существует ли уже страница с таким URL для данного сайта
                $existingPage = Page::where('site_id', $site->id)->where('url', $link)->first();

                if (!$existingPage) {
                    // Если страницы с таким URL еще нет, создайте новую запись
                    $page = new Page();
                    $page->site_id = $site->id;
                    $page->url = $link;
                    $page->save();

                    try {
                        $link = str_replace(' ', '', $link);
                        // Попробуйте получить заголовки HTTP-ответа
                        $headers = get_headers($link);
                        
                        if (strpos($headers[0], '200 OK') !== false) {
                            $sitemapContent = file_get_contents($link);

                            // Удаление <script> блоков с JavaScript кодом
                            $sitemapContent = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $sitemapContent);
                            // Удаление <style> блоков с CSS стилями
                            $sitemapContent = preg_replace('/<style\b[^>]*>.*?<\/style>/is', '', $sitemapContent);

                            // Удаление <header> и </header> тегов
                            $sitemapContent = preg_replace('/<header\b[^>]*>.*?<\/header>/is', '', $sitemapContent);

                            // Удаление <footer> и </footer> тегов
                            $sitemapContent = preg_replace('/<footer\b[^>]*>.*?<\/footer>/is', '', $sitemapContent);

                            // Удалить HTML-комментарии из строки
                            $sitemapContent = preg_replace('/<!--(.|\s)*?-->/', '', $sitemapContent);

                            // Удаление блоков с классами или id, содержащими "header" или "footer"
                            //$sitemapContent = preg_replace('/<[^>]*\b(class|id)\s*=\s*["\'].*?(header|footer).*?["\'][^>]*>.*?<\/[^>]*>/is', '', $sitemapContent);
                            
                            # delete header and footer tags in $sitemapContent and delete selectors class, id "header" and "footer"

                            $crawler = new Crawler($sitemapContent);
                            $pageText = $crawler->text();

                            // Сохраните текст в таблице contents
                            $content = new Content();
                            $content->page_id = $page->id;
                            $content->text = $pageText;
                            $content->save();
                        } else {
                            // Страница недоступна, пропустите ее
                            continue;
                        }
                    }   catch (Exception $e) {
                        // В случае ошибки просто перейдите к следующей странице
                        continue;
                    }

                }
            }

            return response()->json(['text' => 'Выполнено успешно, данные сохранены']);
        }
    }

    public function test(Request $request) {
        $content = $this->apiService->fetchContentFromDatabase('https://ss.kg/sitemap.xml');
        return response()->json(['text' => $content]);
    }
}