<?php


namespace App\Classes;


use Spatie\Browsershot\Browsershot;
use Spatie\Crawler\Crawler;

class FreeProxyListParser
{
    public static function parse($observerClass, $pageId = 0)
    {
        $baseUrl = "http://spys.one/free-proxy-list/ALL";

        if ($pageId) {
            $baseUrl .= "/{$pageId}";
        }

        Crawler::create()
            ->setBrowsershot((new Browsershot($baseUrl))->selectOption('#xpp', 5)->delay(20000)->waitUntilNetworkIdle())
            ->executeJavaScript()
            ->setCrawlObserver(new $observerClass())
            ->ignoreRobots()
            ->setMaximumDepth(0)
            ->startCrawling($baseUrl);

    }
}
