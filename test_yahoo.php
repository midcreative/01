<?php
$url = "https://tw.news.yahoo.com/search?p=" . urlencode('\"許馨勻\" 屏東');
$options = [
    'http' => [
        'method' => 'GET',
        'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36\r\n"
    ]
];
$context = stream_context_create($options);
$html = @file_get_contents($url, false, $context);
if(!$html) {
    echo "Yahoo blocked.\n";
} else {
    echo "Yahoo length: " . strlen($html) . "\n";
}
