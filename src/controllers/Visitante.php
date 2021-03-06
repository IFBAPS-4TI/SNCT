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

    public function alterarPefilView($request, $response, $args)
    {
        $handler = new DatabaseHandler();
        $token = (array)Util::decodeToken($this->session->get('jwt_token'));
        $usuario = $handler->TokenTranslation($token);
        $request = $request->withAttribute("email", $usuario->getEmail());
        return $this->container->view->render($response, 'panel/visitante/editPerfil.html', $request->getAttributes());
    }
    public function alterarPerfil($request, $response, $args)
    {
        $handler = new DatabaseHandler();
        $params = $request->getParams();
        try {
            $token = (array)Util::decodeToken($this->session->get('jwt_token'));
            $usuario = $handler->TokenTranslation($token);
            $usuario->setEmail($params['email']);
            if(isset($params['senha']) && $params['senha'] != ""){
            $usuario->setSenha($params['senha']);
            }
            $handler->atualizarPerfil($usuario);
            $this->session->delete('jwt_token');
            Flash::message("<strong>Sucesso!</strong> Perfil atualizado com sucesso. Você precisa logar novamente", $type = "success");
            return $response->withStatus(200)->withHeader('Location', $this->container->get('router')->pathFor('entrar', []));
        } catch (Exception $e) {
            Flash::message("<strong>Erro!</strong> {$e->getMessage()}", $type = "error");
        }
        return $response->withStatus(200)->withHeader('Location', $this->container->get('router')->pathFor('visitante.editar', []));
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
            if(count($inscritos) >= $ativData['capacidade'] && (int)$ativData['capacidade'] != 0){
                throw new Exception("Atividade lotada");
            }
            $sessao_timestamp = str_replace("U", " ", $sessaoData['timestamp_ativ']);
            $sessao_timestamp = str_replace('/', '-', $sessao_timestamp);
            $sessao_inicio = strtotime(date('d-m-Y H:i', strtotime($sessao_timestamp)));
            if(time() > $sessao_inicio){
                throw new Exception("Esta atividade já acabou");
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