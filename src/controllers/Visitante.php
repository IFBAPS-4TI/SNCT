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
        $token = (array)Util::decodeToken($this->session->get('jwt_token'));
        $usuario = $handler->TokenTranslation($token);
        $request = $request->withAttribute("inscricoes", $handler->getInscricoesByIdUsuario($usuario->getId()));
        return $this->container->view->render($response, 'panel/visitante/listInscricoes.html', $request->getAttributes());
    }

    public function removerInscricao($request, $response, $args)
    {
        $handler = new DatabaseHandler();
        try {
            $token = (array)Util::decodeToken($this->session->get('jwt_token'));
            $usuario = $handler->TokenTranslation($token);
            if ($handler->removeInscricaoTrava($args['id_inscricao'], $usuario->getId())) {
                Flash::message("<strong>Sucesso!</strong> Atividade atualizada com sucesso.", $type = "success");
            } else {
                throw new Exception("Não foi possível remover a inscrição.");
            }
        } catch (Exception $e) {
            Flash::message("<strong>Erro!</strong> {$e->getMessage()}", $type = "error");
        }
        return $response->withStatus(200)->withHeader('Location', $this->container->get('router')->pathFor('visitante.lista', []));
    }

    public function adicionarInscricao($request, $response, $args)
    {
        $handler = new DatabaseHandler();
        try {
            $token = (array)Util::decodeToken($this->session->get('jwt_token'));
            $usuario = $handler->TokenTranslation($token);
            $inscricoes = $handler->getInscricoesByIdUsuario($usuario->getId());
            $sessaoData = $handler->getSessaoDataByIdSessao((int)$args['id_sessao']);
            if (count($sessaoData) < 1) {
                throw new Exception("Sessão não encontrada");
            }
            $ativData = $handler->getAtivDataById($sessaoData['id_atividade']);
            foreach ($inscricoes as $inscricao) {
                if($inscricao['id_sessao'] == $args['id_sessao']){
                    throw new Exception("Você já se registrou nessa sessão.");
                }
            }
            $inscritos = $handler->getInscritosBySessionId($args['id_sessao']);
            if(count($inscritos) >= $ativData['capacidade']){
                throw new Exception("Atividade lotada");
            }
            if(!$handler->addInscricao($usuario->getId(), (int)$args['id_sessao'])){
                throw new Exception("Algo deu errado ao adicionar inscrição");
            }
            Flash::message("<strong>Sucesso!</strong> Você foi inscrito.", $type = "success");
        } catch (Exception $e) {
            Flash::message("<strong>Erro!</strong> {$e->getMessage()}", $type = "error");
        }
        return $response->withStatus(200)->withHeader('Location', $this->container->get('router')->pathFor('painel', []));
    }
}