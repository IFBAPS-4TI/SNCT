<?php

use Tamtamchik\SimpleFlash\Flash;

class Admin
{
    protected $container;
    protected $session;

    public function __construct(Slim\Container $container)
    {
        $this->container = $container;
        $this->session = new \SlimSession\Helper;
    }

    public function addAtiv($request, $response, $args)
    {
        $params = $request->getParams();
        try {
            $atividade = new \Models\Atividade();
            $atividade->setNome($params['title']);
            $atividade->setDescricao($params['desc']);
            $atividade->setTipo($params['tipo']);
            $atividade->setDuracao($params['duration']);
            if (isset($params['email'])) {
                $atividade->setOrganizador($params['email']);
            }
            $atividade->setCapacidade($params['capacity']);
            if ($params['certificado'] == "on") {
                $atividade->setCertificado(true);
            }
            /*
             * Adicionar sessões depois
             */
            $sessoes = array();
            for ($i = 1; $i <= 5; $i++) {
                if (isset($params['hora-' . $i]) && isset($params['data-' . $i])) {
                    $sessao = new \Models\Sessao($params['data-' . $i], $params['hora-' . $i], $params['local-' . $i]);
                    $sessoes[] = $sessao;
                }
            }
            $atividade->setSessoes($sessoes);
            $handler = new DatabaseHandler();
            $handler->addAtividade($atividade);
            Flash::message("<strong>Sucesso!</strong> Atividade criada com sucesso.", $type = "success");
        } catch (Exception $e) {
            Flash::message("<strong>Erro!</strong> {$e->getMessage()}", $type = "error");
        }
        return $this->container->view->render($response, 'panel/admin/addAtiv.html', $request->getAttributes());
    }

    public function addAdmin($request, $response, $args)
    {
        $params = $request->getParams();
        $handler = new DatabaseHandler();
        try {
            if (!\Respect\Validation\Validator::email()->validate($params['email'])) {
                throw new Exception("Email inválido.");
            }
            $data = $handler->getDataByEmail($params['email']);
            if (count($data) > 1) {
                if ($handler->checkAdmin($data["id_usuario"])) {
                    throw new Exception("Usuário já é administrador.");
                }
                if ($handler->addAdmin($data["id_usuario"])) {
                    Flash::message("<strong>Ok!</strong> Garantido permissões de administrador para o usuário escolhido.", $type = "success");
                } else {
                    throw new Exception("Algo deu errado. Não foi possível elevar os privilégios deste usuário");
                }
            } else {
                throw new Exception("Usuário não encontrado. Ele está cadastrado?");
            }
        } catch (Exception $e) {
            Flash::message("<strong>Erro!</strong> {$e->getMessage()}", $type = "error");
        }

        return $this->container->view->render($response, 'panel/admin/addAdmin.html', $request->getAttributes());
    }

    public function addAdminView($request, $response, $args)
    {
        return $this->container->view->render($response, 'panel/admin/addAdmin.html', $request->getAttributes());
    }

    public function addAtivView($request, $response, $args)
    {
        return $this->container->view->render($response, 'panel/admin/addAtiv.html', $request->getAttributes());
    }
    public function editAtiv($request, $response, $args)
    {
        $handler = new DatabaseHandler();
        $params = $request->getParams();
        try {
            $atividade = new \Models\Atividade();
            $atividade->setId($args['id']);
            $atividade->setNome($params['title']);
            $atividade->setDescricao($params['desc']);
            $atividade->setDuracao($params['duration']);
            if (isset($params['email'])) {
                $atividade->setOrganizador($params['email']);
            }
            $atividade->setCapacidade($params['capacity']);
            if ($params['certificado'] == "on") {
                $atividade->setCertificado(true);
            }
            $handler->editAtiv($atividade);
            Flash::message("<strong>Sucesso!</strong> Atividade atualizada com sucesso.", $type = "success");
        } catch (Exception $e) {
            Flash::message("<strong>Erro!</strong> {$e->getMessage()}", $type = "error");
        }
        $request = $request->withAttribute("ativInfo", $handler->getAtivDataById($args['id']));
        $request = $request->withAttribute("sessoesInfo", $handler->getSessaoDataById($args['id']));
        $request = $request->withAttribute("monitorInfo", $handler->getDataById($handler->getMonitorDataByAtividade($args['id'])[0]['id_usuario']));

        return $this->container->view->render($response, 'panel/admin/editAtiv.html', $request->getAttributes());
    }
    public function editAtivView($request, $response, $args)
    {
        $handler = new DatabaseHandler();
        $request = $request->withAttribute("ativInfo", $handler->getAtivDataById($args['id']));
        $request = $request->withAttribute("sessoesInfo", $handler->getSessaoDataById($args['id']));
        $request = $request->withAttribute("monitorInfo", $handler->getDataById($handler->getMonitorDataByAtividade($args['id'])[0]['id_usuario']));
        return $this->container->view->render($response, 'panel/admin/editAtiv.html', $request->getAttributes());
    }

    public function listAdminView($request, $response, $args)
    {
        $handler = new DatabaseHandler();
        $request = $request->withAttribute("adminList", $handler->listAdmin());
        return $this->container->view->render($response, 'panel/admin/listAdmin.html', $request->getAttributes());
    }

    public function listAtivView($request, $response, $args)
    {
        $handler = new DatabaseHandler();
        $request = $request->withAttribute("ativList", $handler->listAtividades());
        return $this->container->view->render($response, 'panel/admin/listAtiv.html', $request->getAttributes());
    }


    public function listUsersView($request, $response, $args)
    {
        $handler = new DatabaseHandler();
        $request = $request->withAttribute("userList", $handler->listUsers());
        return $this->container->view->render($response, 'panel/admin/listUsers.html', $request->getAttributes());
    }

    public function removeUser($request, $response, $args)
    {
        $handler = new DatabaseHandler();
        if ($handler->removeUser($args['id'])) {
            Flash::message("<strong>Sucesso!</strong> Usuário foi removido.", $type = "success");
        } else {
            Flash::message("<strong>Erro!</strong> Não foi possível remover o usuário indicado.", $type = "error");
        }
        return $response->withStatus(200)->withHeader('Location', $this->container->get('router')->pathFor('admin.list.users', []));
    }

    public function removeAdmin($request, $response, $args)
    {
        $handler = new DatabaseHandler();
        if ($handler->removeAdmin($args['id'])) {
            Flash::message("<strong>Sucesso!</strong> Usuário foi removido do grupo de administradores", $type = "success");
        } else {
            Flash::message("<strong>Erro!</strong> Não foi possível remover as permissões do usuário indicado.", $type = "error");
        }
        return $response->withStatus(200)->withHeader('Location', $this->container->get('router')->pathFor('admin.list', []));
    }

    public function removeAtiv($request, $response, $args)
    {
        $handler = new DatabaseHandler();
        if ($handler->removeAtiv($args['id'])) {
            Flash::message("<strong>Sucesso!</strong> Atividade foi removida", $type = "success");
        } else {
            Flash::message("<strong>Erro!</strong> Não foi possível remover a atividade.", $type = "error");
        }
        return $response->withStatus(200)->withHeader('Location', $this->container->get('router')->pathFor('admin.list.ativ', []));
    }
}
