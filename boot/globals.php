<?php

if (!function_exists('dd')) {
    function dd()
    {
        foreach (func_get_args() as $arg) {
            echo "<pre>";
            print_r($arg); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo "</pre>";
        }
        die();
    }
}
