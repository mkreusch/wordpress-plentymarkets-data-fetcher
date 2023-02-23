<?php
/**
 * Plugin Name: plentyMarkets Data
 * Plugin URI:
 * Description:
 * Author: Marcus Kreusch
 * Author URI: https://onlineshop.consulting
 * Version: 1.0.0
 * Requires at least: 5.0
 * Tested up to:
 * Text Domain: plentymarkets-data-fetcher
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Required minimums and constants
 */
define('PMDF_VERSION', '1.0.0');
define('PMDF_PLUGIN_URL', untrailingslashit(plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__))));
define('PMDF_PLUGIN_PATH', __DIR__ . '/');
define('PMDF_PLUGIN_NAME', 'plentymarkets-data-fetcher');

add_action('init', function () {
    load_plugin_textdomain('unzer-payments', false, basename(__DIR__) . '/languages');
});

add_action('plugins_loaded', function () {
    require_once PMDF_PLUGIN_PATH . 'includes/Main.php';
    $main = \PlentymarketsDataFetcher\Main::getInstance();
    $main->init();
});
