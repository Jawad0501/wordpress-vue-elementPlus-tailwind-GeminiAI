<?php

require_once(ABSPATH.'wp-admin/includes/upgrade.php');
require_once(FLUENTGEMINI_PLUGIN_PATH.'database/migrations/Setting.php');


class FluentGeminiDBMigrator
{
    public static function run()
    {
        global $wpdb;
        self::migrate();
    }

    public static function migrate()
    {
        \FluentGeminiMigrations\Setting::migrate();
    }
}

FluentGeminiDBMigrator::run();
