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
        $token = (array) Util::decodeToken($this->session->get('jwt_token'));
        $usuario = $handler->TokenTranslation($token);
        $request = $request->withAttribute("inscricoes", $handler->getInscricoesByIdUsuario($usuario->getId()));
        return $this->container->view->render($response, 'panel/visitante/listInscricoes.html', $request->getAttributes());
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