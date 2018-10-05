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
            $sessao_timestamp = str_replace("U", " ", $sessaoData['timestamp_ativ']);
            $sessao_timestamp = str_replace('/', '-', $sessao_timestamp);
            $sessao_inicio = date('d/m/Y H:i', strtotime($sessao_timestamp));
            $sessao_fim = strtotime("+{$ativData['duracao']} minutes", $sessao_inicio);
            foreach ($inscricoes as $inscricao) {
                $data = str_replace("U", " ", $inscricao['timestamp_ativ']);
                $data_inicio = date('d/m/Y H:i', strtotime($data));
                $data_fim = strtotime("+{$inscricao['duracao']} minutes", $data_inicio);
                if(($data_inicio > $sessao_inicio &&  $data_inicio < $sessao_fim) || ($data_fim > $sessao_inicio &&  $data_fim < $sessao_fim)){
                    throw new Exception("Você possui uma sessão com horários conflitantes.");
                }
            }
            Flash::message("<strong>Sucesso!</strong> Você foi inscrito.", $type = "success");
        } catch (Exception $e) {
            Flash::message("<strong>Erro!</strong> {$e->getMessage()}", $type = "error");
        }
        return $response->withStatus(200)->withHeader('Location', $this->container->get('router')->pathFor('painel', []));
    }
}