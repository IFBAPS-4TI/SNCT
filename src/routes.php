<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

$app->get('/', function (Request $request, Response $response, array $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    // Render index view
    return $this->view->render($response, 'home/index.html', $args);
});

$app->group('/painel', function() {
    $this->get('/entrar',  \Painel::class . ':loginView');
    $this->get('/registrar',  \Painel::class . ':registerView');
    $this->get('',  \Painel::class . ':indexView');
});