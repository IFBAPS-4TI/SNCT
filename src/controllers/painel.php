<?php

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
        $data = date('Y-m-d', strtotime($data));
        // Data corrigida, testando.
        $data = strtotime($data);
        $min = strtotime('+13 years', $data);
        if (time() < $min) {
            return false;
        }
        return true;
    }

    public function registrarUsuario($request, $response, $args)
    {
        $params = $request->getParams(); // Pegamos os dados do formulário
        // E aí nós validamos esse formulário
        try {
            if (!\Respect\Validation\Validator::stringType()->notEmpty()->validate($params['inputName'])) {
                throw new Exception("Nome não pode estar vazio!");
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
            if($handler->criarUsuario($usuario)){
                return $this->container->view->render($response, 'panel/login.html', [
                    'info' => "Usuário registrado com sucesso."
                ]);
            }else{
                throw new Exception("Algo deu errado.");
            }
        } catch (Exception $e) {
            // Se houverem erros no formulário, enviar para o template
            return $this->container->view->render($response, 'panel/register.html', [
                'erro' => $e->getMessage()
            ]);
        }
        // Usuário válido
    }

    public function indexView($request, $response, $args)
    {
        try {
            if ($this->session->exists('jwt_token')) {
                $token = JWT::decode(base64_decode($this->session->get('jwt_token')), $_SERVER["jwt_key"], array('HS512'));
            } else {
                throw new Exception('Token não existe');
            }
        } catch (Exception $e) {
            return $response->withStatus(303)->withHeader('Location', $this->container->get('router')->pathFor('entrar', []));
        }
        return $this->container->view->render($response, 'panel/panel.html', $args);
    }

    public function registerView($request, $response, $args)
    {
        return $this->container->view->render($response, 'panel/register.html', $args);
    }

    public function loginView($request, $response, $args)
    {
        return $this->container->view->render($response, 'panel/login.html', $args);
    }
}


#return $this->view->render($response, 'panel/login.html', $args);