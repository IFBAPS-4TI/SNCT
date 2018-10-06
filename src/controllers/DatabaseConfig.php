<?php


class DatabaseConfig
{
    private $url;
    private $usuario;
    private $senha;
    private $pdo;

    /**
     * DatabaseConfig constructor.
     */
    public function __construct()
    {
        $this->url = "mysql:host=". getenv('sql_domain') .";dbname=". getenv('sql_database') .";charset=utf8";
        $this->usuario = getenv('sql_username');
        $this->senha = getenv('sql_password');
        $this->pdo = new \Slim\PDO\Database($this->url, $this->usuario, $this->senha);
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return mixed
     */
    public function getUsuario()
    {
        return $this->usuario;
    }

    /**
     * @return mixed
     */
    public function getSenha()
    {
        return $this->senha;
    }

    /**
     * @return \Slim\PDO\Database
     */
    public function getPdo()
    {
        return $this->pdo;
    }


}