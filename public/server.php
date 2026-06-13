<?php

$publicPath = dirname(__FILE__);

$uri =
    urldecode(
        parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
    );

/*
|--------------------------------------------------------------------------
| Static assets
|--------------------------------------------------------------------------
*/

if (
    $uri !== '/'
    &&
    file_exists($publicPath . $uri)
) {

    $extension =
        pathinfo($publicPath . $uri, PATHINFO_EXTENSION);

    $mimeTypes = [
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'json' => 'application/json',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif'  => 'image/gif',
        'svg'  => 'image/svg+xml',
        'webp' => 'image/webp',
        'ico'  => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2'=> 'font/woff2',
        'ttf'  => 'font/ttf',
    ];

    if (isset($mimeTypes[$extension])) {

        header(
            'Content-Type: '
            . $mimeTypes[$extension]
        );

    }

    readfile($publicPath . $uri);

    exit;

}

/*
|--------------------------------------------------------------------------
| Static HTML cache
|--------------------------------------------------------------------------
*/

$cacheBasePath =
    dirname($publicPath)
    . '/storage/page-cache';

$cacheFile =
    $cacheBasePath
    . rtrim($uri, '/')
    . '/index.html';

/*
|--------------------------------------------------------------------------
| Homepage
|--------------------------------------------------------------------------
*/

if ($uri === '/') {

    $cacheFile =
        $cacheBasePath
        . '/index.html';

}

/*
|--------------------------------------------------------------------------
| Serve cached html
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'GET'
    &&
    file_exists($cacheFile)
) {

    header('Content-Type: text/html; charset=UTF-8');

    header('X-Page-Cache: HIT');

    readfile($cacheFile);

    exit;

}

/*
|--------------------------------------------------------------------------
| Laravel fallback
|--------------------------------------------------------------------------
*/

require_once
    $publicPath . '/index.php';