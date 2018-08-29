<?php
// Application middleware

// e.g: $app->add(new \Slim\Csrf\Guard);
$app->add(new \Slim\Middleware\Session([
    'name' => 'snctcontrol',
    'autorefresh' => true,
    'lifetime' => '1 week'
]));
