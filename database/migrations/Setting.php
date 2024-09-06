<?php

namespace FluentGeminiMigrations;

class Setting
{
    /**
     * Migrate the table.
     *
     * @return void
     */
    public static function migrate()
    {
        global $wpdb;

        $charsetCollate = $wpdb->get_charset_collate();

        $table = $wpdb->prefix .'fg_settings';

        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            $sql = "CREATE TABLE $table (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                title VARCHAR(255) NOT NULL,
                value_given TEXT NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY title (title)
            ) $charsetCollate;";

            dbDelta($sql);
        }
    }
}
