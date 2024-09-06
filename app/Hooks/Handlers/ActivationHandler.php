<?php

namespace FluentGemini\App\Hooks\Handlers;

/**
 * ActivationHandler Class
 *
 *
 * @package FluentGemini\App\Hooks
 *
 * @version 1.0.0
 */
class ActivationHandler
{
    public function handle()
    {
        // Run DB Migrations
        require_once(FLUENTGEMINI_PLUGIN_PATH . 'database/FluentGeminiDBMigrator.php');

    }
}
