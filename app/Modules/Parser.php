<?php


namespace App\Modules;

use App\Models\News;
use App\Models\Log;
use Illuminate\Database\QueryException;
use Symfony\Component\DomCrawler\Crawler;

class Parser
{
    /**
     * Executes a request using the get method.
     * Request url: http://static.feed.rbc.ru/rbc/logical/footer/news.rss
     * Logs the request to the database.
     *
     * @return bool|string response body
     */
    protected function executeRequest() {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => 'http://static.feed.rbc.ru/rbc/logical/footer/news.rss',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.52 Safari/537.17'
        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        $curlInfo = curl_getinfo($curl);

        Log::create([
            'request_url' => $curlInfo['url'],
            'request_method' => 'get',
            'response_body' => $response,
            'response_http_code' => $curlInfo['http_code']
        ]);

        return $response;
    }

    /**
     * Gets news content
     *
     * @return array news data
     */
    public function getContent() {
        $responseBody = $this->executeRequest();

        $crawler = new Crawler($responseBody);
        $items = $crawler->filter('item');
        $newsContents = [];

        foreach ($items as $item) {
            $crawler = new Crawler($item);
            $content = [];

            $content['link'] = $crawler->filter('link')->text();
            $content['name'] = $crawler->filter('title')->text();
            $content['description'] = $crawler->filter('description')->text();
            $content['published_at'] = $crawler->filterXPath('descendant-or-self::pubDate')->text();

            $author = $crawler->filter('author');
            if ($author->count() > 0) $content['author'] = $author->text();

            // First image of news
            $image = $crawler->filter('[type^="image/"]')->first();
            if ($image->count() > 0) $content['image'] = $image->attr('url');

            $newsContents[] = $content;
        }

        return $newsContents;
    }

    /**
     * Saves news
     */
    protected function saveNews() {
        $newsContents = $this->getContent();

        foreach ($newsContents as $newsContent) {
            try {
                News::create($newsContent);
            } catch (QueryException $exception) {
                // If the news has already been saved
                if ($exception->getCode() == '23000') continue;
            }
        }
    }

    public function __invoke() {
        $this->saveNews();
    }
}
