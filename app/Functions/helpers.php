<?php

use FluentGemini\App\App;

if (!function_exists('FluentGemini')) {
    /**
     * @param null|string $module Module name or empty string to get the app of specific moduleFh
     * @return \FluentGemini\App\App $app | object $instance if specific FluentCRM Framework Module
     */
    function FluentGemini($module = null)
    {
        return App::getInstance($module);
    }
}