<?php
require './vendor/autoload.php';
$client = new GuzzleHttp\Client();
for ($i = 1; $i < 70; $i++) {
    $response = $client->get('http://www.ngv.vic.gov.au/explore/collection/collection-areas/?area=australian+painting&from=' . $i);
    $body = (string)$response->getBody();
    preg_match_all('/\/\/www.ngv.vic.gov.au\/explore\/collection\/work\/(\d+)/', $body, $matches);
    $promises = [];
    $dir = dirname(__FILE__);
    foreach ($matches[0] as $match => $url) {
        $fullUrl = 'http:' . $url;
        $id = $matches[1][$match];
        $imageUrl = 'http://content.ngv.vic.gov.au/retrieve.php?size=1280&type=image&vernonID=' . $matches[1][$match];
        echo "Fetching {$fullUrl}\n";
        $promises[] = $client->getAsync($fullUrl)->then(function ($value) use ($id, $dir) {
            file_put_contents($dir . '/html/' . $id . '.html', $value->getBody());
        });
        $imagePromises[] = $client->getAsync($imageUrl)->then(function ($value) use ($id, $dir) {
            file_put_contents($dir . '/images/' . $id . '.jpg', $value->getBody());
        });
    }
    $results = GuzzleHttp\Promise\settle($promises)->wait();
    $imageResults = GuzzleHttp\Promise\settle($imagePromises)->wait();
}
