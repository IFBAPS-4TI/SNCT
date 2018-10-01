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

$app->group('/painel', function () {
    $this->get('/entrar', \Painel::class . ':loginView')->setName('entrar');
    $this->get('/registrar', \Painel::class . ':registerView')->setName('registrar');
    $this->get('[/]', \Painel::class . ':indexView')->setName('painel');
    $this->get('/sair', \Painel::class . ':logoutView')->setName('sair');
    $this->get('/recuperar', \Painel::class . ':forgotView')->setName('resetar');
    /* POST rotas */
    $this->post('/recuperar', \Painel::class . ':resetarSenha');
    $this->post('/registrar', \Painel::class . ':registrarUsuario');
    $this->post('/entrar', \Painel::class . ':logarUsuario');
});

$app->group('/painel/admin', function () {
    $this->get('/add', \Admin::class . ':addAdminView')->setName('admin.add');
    $this->get('/add/ativ', \Admin::class . ':addAtivView')->setName('admin.add.ativ');
    $this->get('/list/ativ', \Admin::class . ':listAtivView')->setName('admin.list.ativ');
    $this->get('/list/ativ/{id}/sessions', \Admin::class . ':listSessionsView')->setName('admin.list.sessions');
    $this->get('/list', \Admin::class . ':listAdminView')->setName('admin.list');
    $this->get('/list/users', \Admin::class . ':listUsersView')->setName('admin.list.users');
    $this->get('/edit/ativ/{id}', \Admin::class . ':editAtivView')->setName('admin.edit.ativ');
    /* API */
    $this->get('/list/remove/{id}', \Admin::class . ':removeAdmin')->setName('admin.remove');
    $this->get('/list/remove/user/{id}', \Admin::class . ':removeUser')->setName('admin.remove.users');
    $this->get('/list/remove/ativ/{id}', \Admin::class . ':removeAtiv')->setName('admin.remove.ativ');
    /* POST rotas */
    $this->post('/add/ativ', \Admin::class . ':addAtiv');
    $this->post('/add', \Admin::class . ':addAdmin');
    $this->post('/edit/ativ/{id}', \Admin::class . ':editAtiv');
})->add($adminOnly);