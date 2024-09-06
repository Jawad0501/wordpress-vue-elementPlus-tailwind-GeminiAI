<?php

use FluentGemini\Framework\Foundation\Application;
use FluentGemini\App\Hooks\Handlers\ActivationHandler;

return function ($file) {
    
    register_activation_hook($file, function () { (new ActivationHandler)->handle();});
    
    // register_deactivation_hook($file, (new DeactivationHandler)->handle());

    // add_action('plugins_loaded', function () use ($file) {
    //     $app = new Application($file);
    //     // require_once FLUENTGEMINI_PLUGIN_PATH . 'app/Functions/helpers.php';
    // });

    add_action('plugins_loaded', function () use ($file) {
        $app = new Application($file);
        require_once FLUENTGEMINI_PLUGIN_PATH . 'app/Functions/helpers.php';
    });
};
