<?php
$rssUrl = "https://news.google.com/rss/search?q=%22%E8%A8%B1%E9%A6%A8%E5%8B%BB%22+AND+%22%E5%B1%8F%E6%9D%B1%22&hl=zh-TW&gl=TW&ceid=TW:zh-Hant";
$proxyUrl = "https://api.allorigins.win/raw?url=" . urlencode($rssUrl);
echo "Proxy URL: $proxyUrl\n";
$xmlString = @file_get_contents($proxyUrl);
echo "Length: " . strlen($xmlString) . "\n";
$xml = @simplexml_load_string($xmlString);
if ($xml) { echo "Items count: " . count($xml->channel->item) . "\n"; }
