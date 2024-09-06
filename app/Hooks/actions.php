<?php
/**
 * @var \FluentCrm\Framework\Foundation\Application $app
 */

$app->addAction('wp_loaded', 'AdminMenuHandler@init');