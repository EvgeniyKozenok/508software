<?php


namespace App\Classes;


use DOMXPath;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Spatie\Crawler\CrawlObserver;

class FreeProxyObserverPagination extends CrawlObserver
{
    const DEFAULT_PAGINATION_NOT_FOUND_PAGE = 1;

    static $lastPaginationPage = null;
    static $maxRepeats = 5;

    /**
     * @inheritDoc
     */
    public function crawled(UriInterface $url, ResponseInterface $response, ?UriInterface $foundOnUrl = null)
    {
        $dom = null;
        $trNodes = FreeProxyListParserHelper::getTrNodes($response, $dom);

        if ($trNodes->count() == 30) { // Browsershot don't select necessary rows - try repeat
            self::$maxRepeats--;
            if (!self::$maxRepeats--) {
                echo 'Repeat later';
                exit;
            }
            return false;
        }

        $paginationXpath = new DomXPath($dom);
        $paginationPages = $paginationXpath->query('//a[starts-with(@href, "/free-proxy-list/ALL")]/font[@class="spy3"][last()]');

        if ($paginationPages->count()) {
            self::$lastPaginationPage = (int) $paginationPages->item(0)->nodeValue;
            return true;
        }

        self::$lastPaginationPage = self::DEFAULT_PAGINATION_NOT_FOUND_PAGE;
        return true;
    }

    /**
     * @inheritDoc
     */
    public function crawlFailed(UriInterface $url, RequestException $requestException, ?UriInterface $foundOnUrl = null)
    {
        dd($requestException);
    }
}
