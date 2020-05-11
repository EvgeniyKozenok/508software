<?php


namespace App\Classes;


use DOMNodeList;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Spatie\Crawler\CrawlObserver;

class FreeProxyObserverList extends CrawlObserver
{
    static $result = [];
    static $paginateRequest = false;

    public static function setPaginationRequest()
    {
        self::$paginateRequest = true;
    }

    /**
     * @inheritDoc
     */
    public function crawled(UriInterface $url, ResponseInterface $response, ?UriInterface $foundOnUrl = null)
    {
        $trNodes = FreeProxyListParserHelper::getTrNodes($response);
        if (!self::$paginateRequest && $trNodes->count() == 30) { // Browsershot don't select necessary rows - try repeat
            echo "Browsershot don\'t select necessary rows - try repeat...";
            return false;
        }

        foreach ($trNodes as $i => $trNode) {
            /**
             * @var $rowCells DOMNodeList
             */
            $rowCells = $trNode->getElementsByTagName('td');
            if ($rowCells->count() != 10) {
                continue;
            }

            $ipVsPort = explode(
                ' - ',
                preg_replace('/(([0-9]{1,3}[\.]){3}[0-9]{1,3}).*:(\d+)/i', '$1 - $3', $rowCells->item(0)->nodeValue)
            );

            self::$result[] = [
                'ip' => $ipVsPort[0] ?? '',
                'port' => $ipVsPort[1] ?? '',
                'type' => $rowCells->item(1)->nodeValue,
                'anonymity' => $rowCells->item(2)->nodeValue,
                'country' => $rowCells->item(3)->nodeValue,
            ];
        }

    }

    /**
     * @inheritDoc
     */
    public function crawlFailed(UriInterface $url, RequestException $requestException, ?UriInterface $foundOnUrl = null)
    {
        dd($requestException);
    }
}
