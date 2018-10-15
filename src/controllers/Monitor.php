<?php
use Tamtamchik\SimpleFlash\Flash;
class Monitor
{
    protected $container;
    protected $session;

    public function __construct(Slim\Container $container)
    {
        $this->container = $container;
        $this->session = new \SlimSession\Helper;
    }

    public function listAtivView($request, $response, $args)
    {
        $handler = new DatabaseHandler();
        $token = (array) Util::decodeToken($this->session->get('jwt_token'));
        $usuario = $handler->TokenTranslation($token);
        $monitorias = array();
        foreach($usuario->getMonitorias() as $monitoria){
            $monitorias[] = $handler->getAtivDataById($monitoria);
        }
        $request = $request->withAttribute("monitorList", $monitorias);
        return $this->container->view->render($response, 'panel/monitor/listAtiv.html', $request->getAttributes());
    }
    public function editAtivView($request, $response, $args)
    {
        $handler = new DatabaseHandler();
        if (count($handler->getAtivDataById($args['id_atividade'])) <= 1) {
            Flash::message("<strong>Erro!</strong> Atividade não encontrada", $type = "error");
            return $response->withStatus(200)->withHeader('Location', $this->container->get('router')->pathFor('monitor.list', []));
        }
        $request = $request->withAttribute("ativInfo", $handler->getAtivDataById($args['id_atividade']));
        return $this->container->view->render($response, 'panel/monitor/editAtiv.html', $request->getAttributes());
    }
    public function listInscriView($request, $response, $args)
    {
        $handler = new DatabaseHandler();
        if (count($handler->getAtivDataById($args['id_atividade'])) <= 1) {
            Flash::message("<strong>Erro!</strong> Atividade não encontrada", $type = "error");
            return $response->withStatus(200)->withHeader('Location', $this->container->get('router')->pathFor('monitor.list', []));
        }
        $dados = array();
        $sessoes =  $handler->getSessaoDataById($args['id_atividade']);
        $count = 1;
        foreach($sessoes as $sessao){
            $inscritos = $handler->getInscritosBySessionId($sessao['id_sessao']);
            foreach($inscritos as $inscrito){

                $dado = array($handler->getDataById($inscrito['id_usuario']));
                $dado[0]['compareceu'] = $inscrito['compareceu'];
                $dado[0]['sessao'] = $sessao['id_sessao'];
                $dado[0]['id_inscricao'] = $inscrito['id_inscricao'];
                $dados[$count][] = $dado;
            }
            $count++;
        }
        $request = $request->withAttribute("inscritos", $dados);
        $request = $request->withAttribute("id_atividade", $args['id_atividade']);
        return $this->container->view->render($response, 'panel/monitor/listInscri.html', $request->getAttributes());
    }
    public function listPresencaView($request, $response, $args)
    {
        $handler = new DatabaseHandler();
        if (count($handler->getAtivDataById($args['id_atividade'])) <= 1) {
            Flash::message("<strong>Erro!</strong> Atividade não encontrada", $type = "error");
            return $response->withStatus(200)->withHeader('Location', $this->container->get('router')->pathFor('monitor.list', []));
        }
        $dados = array();
        $sessoes =  $handler->getSessaoDataById($args['id_atividade']);
        $count = 1;
        foreach($sessoes as $sessao){
            $inscritos = $handler->getInscritosBySessionId($sessao['id_sessao']);
            foreach($inscritos as $inscrito){
                $dado = array($handler->getDataById($inscrito['id_usuario']));
                $dado[0]['sessao'] = $sessao['id_sessao'];
                $dados[$count][] = $dado;
            }
            $count++;
        }
        $request = $request->withAttribute("inscritos", $dados);
        $request = $request->withAttribute("id_atividade", $args['id_atividade']);
        return $this->container->view->render($response, 'panel/monitor/listPresenca.html', $request->getAttributes());
    }
    public function editAtiv($request, $response, $args)
    {
        $handler = new DatabaseHandler();
        $params = $request->getParams();
        try {
            $atividade = new \Models\Atividade();
            $atividade->setId($args['id_atividade']);
            $atividade->setNome($params['title']);
            $atividade->setDescricao($params['desc']);
            $handler->editAtivMonitor($atividade);
            Flash::message("<strong>Sucesso!</strong> Atividade atualizada com sucesso.", $type = "success");
        } catch (Exception $e) {
            Flash::message("<strong>Erro!</strong> {$e->getMessage()}", $type = "error");
        }
        $request = $request->withAttribute("ativInfo", $handler->getAtivDataById($args['id_atividade']));
        return $this->container->view->render($response, 'panel/monitor/editAtiv.html', $request->getAttributes());
    }
    public function deletarInscricao($request, $response, $args)
    {
        $handler = new DatabaseHandler();
        try {
            if(!$handler->removeInscricao($args['id_inscricao'])){
                throw new Exception("Não foi possível remover a inscrição");
            }

            Flash::message("<strong>Sucesso!</strong> Inscrição removida com sucesso.", $type = "success");
        } catch (Exception $e) {
            Flash::message("<strong>Erro!</strong> {$e->getMessage()}", $type = "error");
        }
        return $response->withStatus(200)->withHeader('Location', $this->container->get('router')->pathFor('monitor.list.inscri', [
            'id_atividade' => $args['id_atividade']
        ]));
    }
    public function atualizarFrequencia($request, $response, $args)
    {
        $handler = new DatabaseHandler();
        try {
            $handler->atualizarFrequencia($args['id_inscricao'], $args['valor']);

            Flash::message("<strong>Sucesso!</strong> Inscrição atualizada com sucesso.", $type = "success");
        } catch (Exception $e) {
            Flash::message("<strong>Erro!</strong> {$e->getMessage()}", $type = "error");
        }
        return $response->withStatus(200)->withHeader('Location', $this->container->get('router')->pathFor('monitor.list.inscri', [
            'id_atividade' => $args['id_atividade']
        ]));
    }
}