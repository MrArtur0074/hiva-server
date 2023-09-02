<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Http\Response;
use Symfony\Component\Process\Process;
use App\Models\Site;
use App\Models\Page;

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

            $sitemapUrl = $request->input('url');

            // Проверьте, существует ли сайт с таким URL
            $site = Site::where('url', $sitemapUrl)->first();

            if (!$site) {
                // Если сайта с таким URL нет, создайте новую запись
                $site = new Site();
                $site->url = $sitemapUrl;
                $site->save();
            }
            
            $pattern = '/(?:<loc>)?(https?:\/\/[^<>\s]+)(?:<\/loc>)?/i';

            $sitemapContent = file_get_contents($sitemapUrl);

            //$sitemap = new Crawler($sitemapContent);

            // Извлеките все URL из sitemap
            $links = [];

            if (preg_match_all($pattern, $sitemapContent, $matches)) {
                $links = $matches[0]; // Используйте $matches[0] для ссылок
            } else {
                $links = [];
            }

            // Перебор массива ссылок
            foreach ($links as &$link) {
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
                }
            }

            return response()->json(['links' => $links]);
        }
    }
}