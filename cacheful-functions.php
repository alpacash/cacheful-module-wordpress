<?php

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

function cacheful_init()
{
    //
}

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
