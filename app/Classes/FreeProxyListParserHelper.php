<?php


namespace App\Classes;


use DOMDocument;
use DOMXPath;
use Psr\Http\Message\ResponseInterface;

class FreeProxyListParserHelper
{

    public static function getTrNodes(ResponseInterface $response, &$dom = null)
    {
        $dom = new DOMDocument();
        @$dom->loadHTML($response->getBody());

        $xpath = new DomXPath($dom);
        return $xpath->query("(//tr[@class='spy1xx'][position() > 1] | //tr[@class='spy1x'][position() > 1])");
    }

}
