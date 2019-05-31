<?php
/*
Plugin Name: Cacheful Client
Description: Wordpress caching module integrated with cacheful.app
Version: 0.1.0
Author: Cacheful
Author URI: https://cacheful.app/
Text Domain: cachefulclient
*/

require_once(WP_CONTENT_DIR . '/plugins/cacheful-client/vendor/autoload.php');
require_once(WP_CONTENT_DIR . '/plugins/cacheful-client/cacheful-simulate-slow.php');
require_once(WP_CONTENT_DIR . '/plugins/cacheful-client/cacheful-config.php');

function cacheful_add_pages()
{
    add_options_page('Cacheful Client', 'Cacheful Client', 'manage_options', 'cachefulclient',
        '\manage_cacheful_settings');
}

function manage_cacheful_settings()
{
    echo '<h2>Cacheful Client</h2>';

    if (isset($_GET['warmup-status-code'])) {
        if ($_GET['warmup-status-code'] == 200) {
            echo '<div class="notice notice-success"><h4>Successfully intiated warm-up for this project.</h4></div>';
        } else {
            echo '<div class="notice notice-error"><h4>Warm-up is already running. Please wait for this process to finish.</h4></div>';
        }
    }

    if (isset($_GET['cache-cleared'])) {
        echo '<div class="notice notice-success"><h4>Successfully cleared all cache.</h4></div>';
    }

    echo '<p>This Cacheful module is installed and maintained by <a target="_blank" href="https://www.abbatis.nl/">abbatis</a>.</p>'
        . '<p>Manual configuration is not available. For technical support, please contact us.</p>';
}

add_action('admin_menu', 'cacheful_add_pages');

function cache_page()
{
    if (
        $_SERVER['REQUEST_METHOD'] !== 'GET'
        || !isConfigured()
    ) {
        return;
    }

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

function cacheful_close()
{
    $cachefile = WP_CONTENT_DIR . '/cache/' . md5($_SERVER['REQUEST_URI']) . date('M-d-Y') . '.php';

    $fp = fopen($cachefile, 'w');
    fwrite($fp, ob_get_contents());
    fclose($fp);

    ob_end_flush();
}

add_action('wp_footer', 'cacheful_close');

function cacheful_init()
{
    if (!is_user_logged_in()) {
        cache_page();
    }
}

add_action('init', 'cacheful_init');

function cacheful_clear()
{
    $files = glob(WP_CONTENT_DIR . '/cache/*');

    foreach($files as $file){
        if(is_file($file)){
            unlink($file);
        }
    }

    header("Location: /wp-admin/options-general.php?page=cachefulclient&cache-cleared=1"); exit;
}

if (isset($_GET['cacheful-clear'])) {
    cacheful_clear();
}

function cacheful_warmup()
{
    if (!isConfigured()) {
        return;
    }

    try {
        $warmupStatuscode = (new \GuzzleHttp\Client())
            ->request(
                'POST',
                sprintf('https://cacheful.app/api/projects/%s/process', getCachefulProjectId()),
                ['headers' => ['Authorization' => 'Bearer ' . getCachefulApiToken()]]
            )->getStatusCode();
        header("Location: /wp-admin/options-general.php?page=cachefulclient&warmup-status-code={$warmupStatuscode}"); exit;
    } catch (\GuzzleHttp\Exception\GuzzleException $e) {
        header("Location: /wp-admin/options-general.php?page=cachefulclient&warmup-status-code=409"); exit;
    }
}

if (isset($_GET['cacheful-warmup'])) {
    cacheful_warmup();
}

add_action('admin_bar_menu', 'add_toolbar_items', 100);
function add_toolbar_items($admin_bar)
{
    $admin_bar->add_menu([
        'id' => 'clear-cache',
        'title' => 'Clear Cache',
        'href' => '/wp-admin/options-general.php?page=cachefulclient&cacheful-clear=1',
        'meta' => [
            'title' => __('Clear Cache'),
        ],
    ]);

    $admin_bar->add_menu([
        'id' => 'warmup',
        'title' => 'Warm-up',
        'href' => '/wp-admin/options-general.php?page=cachefulclient&cacheful-warmup=1',
        'meta' => [
            'title' => __('Clear Cache'),
        ],
    ]);
}
