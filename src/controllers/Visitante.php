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
    public function removerInscricao($request, $response, $args)
    {
        $handler = new DatabaseHandler();
        try {
            $token = (array) Util::decodeToken($this->session->get('jwt_token'));
            $usuario = $handler->TokenTranslation($token);
            if($handler->removeInscricaoTrava($args['id_inscricao'], $usuario->getId())){
            Flash::message("<strong>Sucesso!</strong> Atividade atualizada com sucesso.", $type = "success");
            }else{
                throw new Exception("Não foi possível remover a inscrição.");
            }
        } catch (Exception $e) {
            Flash::message("<strong>Erro!</strong> {$e->getMessage()}", $type = "error");
        }
        return $response->withStatus(200)->withHeader('Location', $this->container->get('router')->pathFor('visitante.lista', []));
    }
}