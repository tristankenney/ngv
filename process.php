<?php

require 'vendor/autoload.php';

$info = ['Medium' => '', 'Measurements' => '', 'Accession Number' => '', 'Place/s of Execution' => '', 'Edition' => ''];

file_put_contents('out.csv', "ID, Link, Title, Year, Artists, Medium, Measurements, Accession Number, Place of Execution, Edition\n");
$csv = fopen('out.csv', 'a+');

foreach (new DirectoryIterator('./html') as $fileInfo) {
    if($fileInfo->isDot()) continue;
    $contents = file_get_contents('/Users/tristan/ngv/html/' . $fileInfo->getFilename());
    $row = [];
    preg_match_all('/\<h1.*\>([\D\d]+)\<\/h1\>/', $contents, $matches);
    $dom = new PHPHtmlParser\Dom;
    $dom->load($matches[1][0]);

    $row[] = str_replace('.html', '',$fileInfo->getFilename());
    $row[] = $dom->find('em')[0]->text;
    $row[] = str_replace(['(', ')'], '', $dom->find('span')[0]->text);
    $row[] = implode(',',array_map(function($author) {
        return trim($author->text);
    }, $dom->find('ul li a')->toArray()));

    preg_match_all('/<dl.*\>([\D\d]+)\<\/dl\>/', $contents, $matches);
    $dom = new PHPHtmlParser\Dom;
    $dom->load($matches[1][0]);
    $row = $row + $info;
    foreach($dom->find('dt')->toArray() as $i => $child) {
       $text = trim($child->text);
        if (isset($info[$text])) {
           $row[$text] = str_replace('&times;', 'x', $child->nextSibling()->nextSibling()->text);
        }
    }
    fputcsv($csv, $row);
}

fclose($csv);
