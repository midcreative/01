<?php
$term = '"許馨勻"';
$rawQuery = $term . ' AND "屏東"';
$query = urlencode($rawQuery);
$rssUrl = "https://news.google.com/rss/search?q={$query}&hl=zh-TW&gl=TW&ceid=TW:zh-Hant";
echo "URL: $rssUrl\n";

$ch = curl_init($rssUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36");
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$xmlString = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
if (!$xmlString) {
    echo "Failed to fetch\n";
} else {
    echo "Length: " . strlen($xmlString) . "\n";
    $xml = @simplexml_load_string($xmlString);
    if (!$xml) { 
        echo "Invalid XML\n"; 
    } else {
        $count = isset($xml->channel->item) ? count($xml->channel->item) : 0;
        echo "Items count: " . $count . "\n";
        if ($count > 0) {
            echo "First title: " . (string)$xml->channel->item[0]->title . "\n";
        }
    }
}
