<?php defined('ABSPATH') or die;

/**
 * Plugin Name:  FluentGemini - Gemini AI For WordPress
 * Description:  Gemini AI Plugin For Wordpress
 * Version:      1.0.0
 * Author:       Nowshad Jawad
 * Author URI:   https://jawad007.netlify.app
 * License:      MIT
 * Text Domain:  fluent-gemini
 * Domain Path:  /language
 */

if (defined('FLUENTGEMINI')) {
    return;
}

define('FLUENTGEMINI', 'fluent-gemini');
define('FLUENTGEMINI_PLUGIN_URL', plugin_dir_url(__FILE__));
define('FLUENTGEMINI_PLUGIN_PATH', plugin_dir_path(__FILE__));

if (!defined('FLUENTGEMINI_UPLOAD_DIR')) {
    define('FLUENTGEMINI_UPLOAD_DIR', '/fluent-gemini');
}

require __DIR__ . '/vendor/autoload.php';

call_user_func(function ($bootstrap) {
    $bootstrap(__FILE__);
}, require(__DIR__ . '/boot/app.php'));
