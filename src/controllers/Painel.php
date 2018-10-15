<?php

use Tamtamchik\SimpleFlash\Flash;

class Painel
{
    protected $container;
    protected $session;

    public function __construct(Slim\Container $container)
    {
        $this->container = $container;
        $this->session = new \SlimSession\Helper;
    }

    /**
     * Valida se um usuário possui 13 anos, retorna
     * @param $data data no formato d-m-Y
     * @return bool se é maior de idade
     */
    private function validarAniversario($data)
    {
        // Armengue para corrigir data no formato inglês
        $data = str_replace('/', '-', $data);
        $data = date('d-m-Y', strtotime($data));
        // Data corrigida, testando.
        $data = strtotime($data);
        $min = strtotime('+13 years', $data);
        if (time() < $min) {
            return false;
        }
        return true;
    }

    /**
     * Controle responsável por logar o usuário e definir o token.
     * @param $request
     * @param $response
     * @param $args
     * @return mixed
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function logarUsuario($request, $response, $args)
    {
        $params = $request->getParams(); // Pegamos os dados do formulário
        $usuario = new \Models\Usuario();
        $usuario->setEmail($params['inputEmail']);
        $usuario->setSenha($params['inputPassword'], true);
        $handler = new DatabaseHandler();
        try {
            $usuario = $handler->authUsuario($usuario);
            $token = array(
                "id_usuario" => $usuario->getId(),
                "email" => $usuario->getEmail(),
                "isAdmin" => $usuario->getisAdministrador(),
                "iss" => $_SERVER['SERVER_NAME']
            );
            $this->session->set('jwt_token', Util::encodeToken($token));
            return $response->withStatus(200)->withHeader('Location', $this->container->get('router')->pathFor('painel', []));
        } catch (Exception $e) {
            // Se houverem erros no formulário, enviar para o template
            Flash::message("<strong>Erro!</strong> {$e->getMessage()}", $type = "error");
            return $this->container->view->render($response, 'panel/login.html', $args);
        }


    }

    /** Controle responsável por resetar a senha
     * @param $request
     * @param $response
     * @param $args
     * @return mixed
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function resetarSenha($request, $response, $args)
    {
        $params = $request->getParams(); // Pegamos os dados do formulário
        // E aí nós validamos esse formulário
        try {
            if (!\Respect\Validation\Validator::email()->validate($params['inputEmail'])) {
                throw new Exception("Email inválido.");
            }
            $handler = new DatabaseHandler();
            $usuario = new \Models\Usuario();
            $usuario->setCpf($params['inputCPF']);
            $usuario->setEmail($params['inputEmail']);
            $senha = Util::generateRandomString(8);
            $usuario->setSenha($senha);
            if ($handler->alterarSenha($usuario, $senha)) {
                Flash::message("<strong>Sucesso!</strong> Uma nova senha foi enviada para o email cadastrado.", $type = "info");
                return $response->withStatus(302)->withHeader('Location', $this->container->get('router')->pathFor('entrar', []));
            } else {
                throw new Exception("Algo deu errado.");
            }
        } catch (Exception $e) {
            // Se houverem erros no formulário, enviar para o template
            Flash::message("<strong>Erro!</strong> {$e->getMessage()}", $type = "error");
            return $this->container->view->render($response, 'panel/forgot.html', $args);
        }
        // Usuário válido
    }

    /**
     * Controle responsável por criar usuário
     * @param $request
     * @param $response
     * @param $args
     * @return mixed
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function registrarUsuario($request, $response, $args)
    {
        $params = $request->getParams(); // Pegamos os dados do formulário
        // E aí nós validamos esse formulário
        try {
            if (!\Respect\Validation\Validator::stringType()->notEmpty()->validate($params['inputName'])) {
                throw new Exception("Nome não pode estar vazio!");
            }
            if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $params['inputName'])) {
                throw new Exception("Nome contêm caracteres inválidos!");
            }
            if (!\Respect\Validation\Validator::stringType()->contains(' ')->validate($params['inputName'])) {
                throw new Exception("Por favor insira o nome completo!");
            }
            if (!\Respect\Validation\Validator::email()->validate($params['inputEmail'])) {
                throw new Exception("Email inválido.");
            }
            if (!\Respect\Validation\Validator::cpf()->notEmpty()->validate($params['inputCPF'])) {
                throw new Exception("CPF inválido.");
            }
            if (!\Respect\Validation\Validator::stringType()->notEmpty()->length(8, null)->validate($params['inputPassword'])) {
                throw new Exception("Senha deve possuir no mínimo 8 caracteres.");
            }
            if (!\Respect\Validation\Validator::date('d/m/Y')->notEmpty()->validate($params['inputDate'])) {
                throw new Exception("Data de nascimento inválida. Data: {$params['inputDate']}");
            }
            if (!$this->validarAniversario($params['inputDate'])) {
                throw new Exception("Usuário não possui mais de 13 anos.");
            }
            $handler = new DatabaseHandler();
            $usuario = new \Models\Usuario();
            $usuario->setNome($params['inputName']);
            $usuario->setCpf($params['inputCPF']);
            $usuario->setEmail($params['inputEmail']);
            $usuario->setSenha($params['inputPassword']);
            $usuario->setNascimento($params['inputDate']);
            if ($handler->criarUsuario($usuario)) {
                Flash::message('<strong>Sucesso!</strong> Usuário criado com sucesso.', $type = "success");
                return $response->withStatus(302)->withHeader('Location', $this->container->get('router')->pathFor('entrar', []));
            } else {
                throw new Exception("Algo deu errado.");
            }
        } catch (Exception $e) {
            // Se houverem erros no formulário, enviar para o template
            Flash::message("<strong>Erro!</strong> {$e->getMessage()}", $type = "error");
            return $this->container->view->render($response, 'panel/register.html', $args);
        }
        // Usuário válido
    }

    public function forgotView($request, $response, $args)
    {

        if ($this->session->exists('jwt_token')) {
            try {
                $token = Util::decodeToken($this->session->get('jwt_token'));
                return $response->withStatus(302)->withHeader('Location', $this->container->get('router')->pathFor('painel', []));
            } catch (Exception $e) {
                $this->session->delete('jwt_token');
            }
        }
        return $this->container->view->render($response, 'panel/forgot.html', $args);
    }

    public function logoutView($request, $response, $args)
    {

        $this->session->delete('jwt_token');
        return $response->withStatus(302)->withHeader('Location', $this->container->get('router')->pathFor('entrar', []));
    }

    public function indexView($request, $response, $args)
    {
        Flash::message("<strong>Importante!</strong> Se você é um <strong>organizador de atividade</strong> você deve solicitar acesso ao <strong>painel de monitor</strong>. Se você é organizador e <strong>não possui acesso</strong> a este painel <strong>não será possível emitir certificado de organizador da atividade no futuro</strong>. Consulte a comissão o mais cedo possível.", $type = "warning");
        $handler = new DatabaseHandler();
        $request = $request->withAttribute("ativList", $handler->listAtividades());
        return $this->container->view->render($response, 'panel/painel/panel.html', $request->getAttributes());
    }

    public function registerView($request, $response, $args)
    {
        if ($this->session->exists('jwt_token')) {
            try {
                $token = Util::decodeToken($this->session->get('jwt_token'));
                return $response->withStatus(302)->withHeader('Location', $this->container->get('router')->pathFor('painel', []));
            } catch (Exception $e) {
                $this->session->delete('jwt_token');
            }
        }
        return $this->container->view->render($response, 'panel/register.html', $args);
    }

    public function loginView($request, $response, $args)
    {
        if ($this->session->exists('jwt_token')) {
            try {
                $token = Util::decodeToken($this->session->get('jwt_token'));
                return $response->withStatus(302)->withHeader('Location', $this->container->get('router')->pathFor('painel', []));
            } catch (Exception $e) {
                $this->session->delete('jwt_token');
            }
        }
        return $this->container->view->render($response, 'panel/login.html', $args);
    }

    public function atividadeShare($request, $response, $args)
    {
        $handler = new DatabaseHandler();
        try {
            $dadosAtiv = $handler->getAtivDataById($args['id_atividade']);
            if (count($handler->getAtivDataById($args['id_atividade'])) <= 1) {
                throw new Exception("Atividade não encontrada");
            }
            $request = $request->withAttribute("ativInfo", $dadosAtiv);
            $request = $request->withAttribute("sessionsList", $handler->listSessoesPorId($args['id_atividade']));
            return $this->container->view->render($response, 'home/atividade.html', $request->getAttributes());
        } catch (Exception $e) {
            Flash::message("<strong>Erro!</strong> {$e->getMessage()}", $type = "error");
            return $response->withStatus(200)->withHeader('Location', $this->container->get('router')->pathFor('painel', []));
        }
    }

}


#return $this->view->render($response, 'panel/login.html', $args);