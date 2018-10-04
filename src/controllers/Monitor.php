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