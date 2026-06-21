<?php
$term = '\"許馨勻\"';
$rawQuery = $term . ' AND \"屏東\"';
$query = urlencode($rawQuery);
$rssUrl = "https://news.google.com/rss/search?q={$query}&hl=zh-TW&gl=TW&ceid=TW:zh-Hant";
echo "URL: $rssUrl<br>";
$options = [
    'http' => [
        'method' => 'GET',
        'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)\r\n"
    ]
];
$context = stream_context_create($options);
$xmlString = @file_get_contents($rssUrl, false, $context);
if (!$xmlString) {
    echo "Failed to fetch. allow_url_fopen=" . ini_get('allow_url_fopen') . "<br>";
    print_r(error_get_last());
} else {
    echo "Fetched " . strlen($xmlString) . " bytes.<br>";
    $xml = @simplexml_load_string($xmlString);
    if (!$xml) { 
        echo "Invalid XML<br>";
        echo htmlspecialchars(substr($xmlString, 0, 500));
    }
    else {
        echo "Items count: " . count($xml->channel->item) . "<br>";
        if (count($xml->channel->item) > 0) {
            echo "First title: " . {$xml->channel->item[0]->title} . "<br>";
        }
    }
}
