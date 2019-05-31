<?php
/*
Plugin Name: Cacheful Client
Description: Wordpress caching module integrated with cacheful.app
Version: 0.1.0
Author: Cacheful
Author URI: https://cacheful.app/
Text Domain: cachefulclient
*/

$pluginPath = plugin_dir_path(__FILE__);
require_once($pluginPath . '/cacheful-config.php');
require_once($pluginPath . '/cacheful-simulate-slow.php');
require_once($pluginPath . '/cacheful-functions.php');
require_once($pluginPath . '/cacheful-serve.php');
require_once($pluginPath . '/vendor/autoload.php');

add_action('init', 'cacheful_init');
add_action('admin_menu', 'cacheful_add_pages');
add_action('admin_bar_menu', 'add_toolbar_items', 100);

if (isset($_GET['cacheful-clear'])) {
    cacheful_clear();
}

if (isset($_GET['cacheful-warmup'])) {
    cacheful_warmup();
}
