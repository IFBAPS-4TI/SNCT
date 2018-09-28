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
                }else{
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
}
