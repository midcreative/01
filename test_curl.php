<?php
$rssUrl = "https://news.google.com/rss/search?q=" . urlencode('\"許馨勻\" AND \"屏東\"') . "&hl=zh-TW&gl=TW&ceid=TW:zh-Hant";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $rssUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36");
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$output = curl_exec($ch);
if($output === false){
    echo 'Curl error: ' . curl_error($ch);
} else {
    echo "Success! Length: " . strlen($output) . "<br>";
    $xml = @simplexml_load_string($output);
    if ($xml) { echo "Items count: " . count($xml->channel->item) . "<br>"; }
}
curl_close($ch);
