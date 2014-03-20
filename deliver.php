<?php

if (empty($_GET['f'])) {
    header('HTTP/1.0 400 Bad Request');
    die('Missing file parameter.');
}

// @TODO Any logic for the concatenation
$file = __DIR__ . '/files/' . trim($_GET['f'], './');

if (!is_readable($file)) {
    header('HTTP/1.0 404 Not Found');
    die('File not found.');
}

// Fallback for pecl_http
if (!function_exists('http_parse_headers')) {
    require __DIR__ . '/lib/http_parse_headers.php';
}

$headers = http_parse_headers(file_get_contents($file));

// @TODO Do some validation on the headers
if ($headers === false || empty($headers['Location'])) {
    header('HTTP/1.0 404 Not Found');
    die('Invalid file.');
}

$location = $headers['Location'];
unset($headers['Location']);

$content = file_get_contents($location);

if ($content === false) {
    header('HTTP/1.0 502 Bad Gateway');
    die('Bad Gateway.');
}

foreach ($headers as $header => $value) {
    if (is_array($value)) {
        foreach ($value as $value2) {
            header("$header: $value2");
        }
    } else {
        header("$header: $value");
    }
}

echo $content;