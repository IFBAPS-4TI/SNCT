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
}