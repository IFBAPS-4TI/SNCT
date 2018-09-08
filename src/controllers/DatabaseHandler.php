<?php


class DatabaseHandler
{
    protected $config;
    private $pdo;

    /**
     * DatabaseHandler constructor.
     */
    public function __construct()
    {
        $this->config = new DatabaseConfig();
        $this->pdo = $this->config->getPdo();
    }

    public function criarUsuario(\Models\Usuario $usuario)
    {
        // Verifica se email ou cpf do usuário já existe
        $select = $this->pdo->select()
            ->from('Usuario')
            ->where('email', '=', $usuario->getEmail())->orWhere('cpf', "=", $usuario->getCpf());
        $stmt = $select->execute();
        $data = $stmt->fetch();
        if (count($data) > 0) {
            throw new Exception("Email ou CPF do usuário já existe.");
        }

        // Tudo certo, inserir usuário
        $insert = $this->pdo->insert(array('nome', 'email', 'nascimento', 'cpf', 'senha'))
            ->into('users')
            ->values(array($usuario->getNome(), $usuario->getEmail(), $usuario->getNascimento(), $usuario->getCpf(), $usuario->getSenha()));
        return $insert->execute(false);;
    }
}