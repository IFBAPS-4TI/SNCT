<?php
use Tamtamchik\SimpleFlash\Flash;
class Visitante
{
    protected $container;
    protected $session;

    public function __construct(Slim\Container $container)
    {
        $this->container = $container;
        $this->session = new \SlimSession\Helper;
    }
    public function listInscricoesView($request, $response, $args)
    {
        $handler = new DatabaseHandler();
        if (count($handler->getAtivDataById($args['id_atividade'])) <= 1) {
            Flash::message("<strong>Erro!</strong> Atividade não encontrada", $type = "error");
            return $response->withStatus(200)->withHeader('Location', $this->container->get('router')->pathFor('monitor.list', []));
        }
        $dados = array();
        $sessoes =  $handler->getSessaoDataById($args['id_atividade']);
        foreach($sessoes as $sessao){
            $inscritos = $handler->getInscritosBySessionId($sessao['id_sessao']);
            foreach($inscritos as $inscrito){
                $dado = array($handler->getDataById($inscrito['id_usuario']));
                $dado[0]['sessao'] = $sessao['id_sessao'];
                $dados[] = $dado;
            }
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
}