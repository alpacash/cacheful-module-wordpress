<?php

function prepare_index_file()
{
    $fileName = $_SERVER['DOCUMENT_ROOT'] . '/index.php';
    $fileContent = file_get_contents($fileName);

    if (preg_match('/(serve_page_end\(\);)/im', $fileContent)) {
        return;
    }

    $fileContent .= "\n\n/** Wrap up the cache bufer **/\n\\serve_page_end();";
    $fileContent = preg_replace("/\n\n+/s","\n\n", $fileContent);

    file_put_contents($fileName, $fileContent);
}

function has_wordpress_cookie()
{
    foreach ($_COOKIE as $key => $value) {
        if (fnmatch('wordpress_*', $key)) {
            return true;
        }
    }

    return false;
}

function should_cache()
{
    return $_SERVER['REQUEST_METHOD'] === 'GET'
        && isConfigured()
        && !has_wordpress_cookie();
}

function serve_page_start()
{
    if (!should_cache()) {
        return;
    }

    prepare_index_file();

    $cachefile = WP_CONTENT_DIR . '/cache/' . md5($_SERVER['REQUEST_URI']) . date('M-d-Y') . '.php';

    $cachetime = 18000;
    if (file_exists($cachefile) && time() - $cachetime < filemtime($cachefile)) {
        readfile($cachefile);
        exit;
    }

    ob_start();

    if (shouldSimulateSlow()) {
        cacheful_simulate_slow();
    }
}

function serve_page_end()
{
    if (!should_cache()) {
        return;
    }

    $cachefile = WP_CONTENT_DIR . '/cache/' . md5($_SERVER['REQUEST_URI']) . date('M-d-Y') . '.php';

    $fp = fopen($cachefile, 'w');
    fwrite($fp, ob_get_contents());
    fclose($fp);

    ob_end_flush();
}

// serve cache if applicable
serve_page_start();
