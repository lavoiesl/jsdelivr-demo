<?php

if (empty($_GET['f'])) {
    header('HTTP/1.0 400 Bad Request');
    die('Missing file parameter.');
}

$file = __DIR__ . '/files/' . trim($_GET['f'], './');

if (!is_readable($file)) {
    header('HTTP/1.0 404 Not Found');
    die('File not found.');
}

if (!function_exists('http_parse_headers')) {
    require __DIR__ . '/lib/http_parse_headers.php';
}

$headers = http_parse_headers(file_get_contents($file));

if ($headers === false || empty($headers['Location'])) {
    header('HTTP/1.0 404 Not Found');
    die('Invalid file.');
}

if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']) && !empty($headers['Last-Modified'])) {
    $file_date = strtotime($headers['Last-Modified']);
    $request_date = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);

    if ($file_date !== false && $request_date !== false && $file_date <= $request_date) {
        header('HTTP/1.0 304 Not Modified');
        die();
    }
}

if (!empty($_SERVER['HTTP_IF_NONE_MATCH']) && !empty($headers['E-Tag'])) {
    if ($_SERVER['HTTP_IF_NONE_MATCH'] == $headers['E-Tag']) {
        header('HTTP/1.0 304 Not Modified');
        die();
    }
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