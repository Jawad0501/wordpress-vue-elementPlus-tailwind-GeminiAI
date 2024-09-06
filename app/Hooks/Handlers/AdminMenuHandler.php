<?php

namespace FluentGemini\App\Hooks\Handlers;

class AdminMenuHandler
{
    public function init()
    {
        add_action('admin_menu', array($this, 'addMenu'));
    }

    public function addMenu()
    {
        add_menu_page(
            'Fluent Gemini',
            'Fluent Gemini',
            'manage_options',
            'fluent-gemini',
            array($this, 'render'),
            'dashicons-lightbulb'
        );

        add_submenu_page(
            'fluent-gemini',                
            'Set API Key',              
            'Set API Key',
            'manage_options',
            'fluent-gemini-set-api-key', // Adjusted slug
            array($this, 'render')
        );
    }

    public function render()
    {
        $this->changeFooter();
        $app = FluentGemini();

        wp_enqueue_script('jquery');
        wp_enqueue_script('fluent_gemini_app_script', FLUENTGEMINI_PLUGIN_URL . 'resource/dist/assets/js/index.js', ['jquery'], '1.0', true);
        wp_enqueue_style('fluent_gemini_app_style', FLUENTGEMINI_PLUGIN_URL . 'resource/dist/assets/css/index.css');

        $app['view']->render('admin.menu_page');
    }

    public function changeFooter()
    {
        add_filter('admin_footer_text', function () {
            return 'Thank you for using <a href="#">FluentGemini</a>.';
        });
    }
}
