<?php
$term = '\"許馨勻\"';
$rawQuery = $term . ' AND \"屏東\"';
$query = urlencode($rawQuery);
$rssUrl = "https://news.google.com/rss/search?q={$query}&hl=zh-TW&gl=TW&ceid=TW:zh-Hant";
echo "URL: $rssUrl\n";
$options = [
    'http' => [
        'method' => 'GET',
        'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)\r\n"
    ]
];
$context = stream_context_create($options);
$xmlString = @file_get_contents($rssUrl, false, $context);
if (!$xmlString) {
    echo "Failed to fetch\n";
} else {
    echo "Length: " . strlen($xmlString) . "\n";
    $xml = @simplexml_load_string($xmlString);
    if (!$xml) { echo "Invalid XML\n"; }
    else {
        echo "Items count: " . count($xml->channel->item) . "\n";
        if (count($xml->channel->item) > 0) {
            echo "First title: " . {$xml->channel->item[0]->title} . "\n";
        }
    }
}
