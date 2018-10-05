<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

$app->get('/', function (Request $request, Response $response, array $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    // Render index view
    return $this->view->render($response, 'home/index.html', $args);
})->setName('home');

$app->get('/view/{id_atividade}', \Painel::class . ':atividadeShare')->setName('atividade');

$app->group('/painel', function () use ($userdata) {
    $this->get('/entrar', \Painel::class . ':loginView')->setName('entrar');
    $this->get('/registrar', \Painel::class . ':registerView')->setName('registrar');
    $this->get('[/]', \Painel::class . ':indexView')->add($userdata)->setName('painel');
    $this->get('/sair', \Painel::class . ':logoutView')->setName('sair');
    $this->get('/recuperar', \Painel::class . ':forgotView')->setName('resetar');
    /* POST rotas */
    $this->post('/recuperar', \Painel::class . ':resetarSenha');
    $this->post('/registrar', \Painel::class . ':registrarUsuario');
    $this->post('/entrar', \Painel::class . ':logarUsuario');
});
$app->group('/painel/visitante', function () use ($userdata) {
    $this->get('/lista', \Visitante::class . ':listInscricoesView')->setName('visitante.lista');
    $this->get('/remover/{id_inscricao}', \Visitante::class . ':removerInscricao')->setName('visitante.remover.inscricao');
    $this->post('/recuperar', \Painel::class . ':resetarSenha');
    $this->post('/registrar', \Painel::class . ':registrarUsuario');
    $this->post('/entrar', \Painel::class . ':logarUsuario');
})->add($userdata);

$app->group('/painel/admin', function () {
    $this->get('/add', \Admin::class . ':addAdminView')->setName('admin.add');
    $this->get('/add/ativ', \Admin::class . ':addAtivView')->setName('admin.add.ativ');
    $this->get('/list/ativ', \Admin::class . ':listAtivView')->setName('admin.list.ativ');
    $this->get('/list/ativ/sessions/{id}', \Admin::class . ':listSessionsView')->setName('admin.list.sessions');
    $this->get('/list', \Admin::class . ':listAdminView')->setName('admin.list');
    $this->get('/list/users', \Admin::class . ':listUsersView')->setName('admin.list.users');
    $this->get('/list/monitor/{id}', \Admin::class . ':listMonitorView')->setName('admin.list.monitor');
    $this->get('/edit/ativ/{id}', \Admin::class . ':editAtivView')->setName('admin.edit.ativ');
    $this->get('/edit/ativ/sessions/{id_ativ}/{id_session}', \Admin::class . ':editSessionView')->setName('admin.edit.sessions');
    /* API */
    $this->get('/remove/{id}', \Admin::class . ':removeAdmin')->setName('admin.remove');
    $this->get('/remove/user/{id}', \Admin::class . ':removeUser')->setName('admin.remove.users');
    $this->get('/remove/monitor/{id_ativ}/{id_usuario}', \Admin::class . ':removeMonitor')->setName('admin.remove.monitor');
    $this->get('/remove/ativ/{id}', \Admin::class . ':removeAtiv')->setName('admin.remove.ativ');
    $this->get('/remove/session/{id_ativ}/{id_session}', \Admin::class . ':removeSession')->setName('admin.remove.session');
    /* POST rotas */
    $this->post('/add/ativ', \Admin::class . ':addAtiv');
    $this->post('/add', \Admin::class . ':addAdmin');
    $this->post('/edit/ativ/{id}', \Admin::class . ':editAtiv');
    $this->post('/list/monitor/{id}', \Admin::class . ':addMonitor');
    $this->post('/edit/ativ/sessions/{id_ativ}/{id_session}', \Admin::class . ':editSession');
})->add($userdata)->add($adminOnly);

$app->group('/painel/monitor', function () use ($monitorOP) {
    $this->get('/list', \Monitor::class . ':listAtivView')->setName('monitor.list');
    $this->group("/{id_atividade}", function(){
        $this->get('/edit', \Monitor::class . ':editAtivView')->setName('monitor.edit.ativ');
        $this->get('/inscritos', \Monitor::class . ':listInscriView')->setName('monitor.list.inscri');
        $this->get('/lista', \Monitor::class . ':listPresencaView')->setName('monitor.list.presenca');
        /* API */
        $this->get('/inscritos/delete/{id_inscricao}', \Monitor::class . ':deletarInscricao')->setName('monitor.inscricao.deletar');
        $this->get('/inscritos/update/{id_inscricao}/{valor}', \Monitor::class . ':atualizarFrequencia')->setName('monitor.inscricao.atualizar');
        /* POST rotas */
        $this->post('/edit', \Monitor::class . ':editAtiv');
    })->add($monitorOP);
})->add($userdata)->add($monitorOnly);