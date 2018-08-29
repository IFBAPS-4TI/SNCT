<?php
// Application middleware

// e.g: $app->add(new \Slim\Csrf\Guard);

$settings = $container->get('settings');
/*
 * SlimSession
 */
$app->add(new \Slim\Middleware\Session([
    'name' => $settings['sessions']['name'],
    'autorefresh' => $settings['sessions']['autorefresh'],
    'lifetime' => $settings['sessions']['lifetime']
]));
