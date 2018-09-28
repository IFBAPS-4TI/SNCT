<?php


namespace Models;


class Atividade
{
    private $nome;
    private $descricao;
    private $tipo;
    private $capacidade;
    private $duracao;
    private $organizador;
    private $sessoes = array();

    /**
     * @return mixed
     */
    public function getNome()
    {
        return $this->nome;
    }

    /**
     * @param mixed $nome
     */
    public function setNome($nome)
    {
        if (!\Respect\Validation\Validator::stringType()->notEmpty()->validate($nome)) {
            throw new Exception("Nome não pode estar vazio!");
        }
        $this->nome = $nome;

    }

    /**
     * @return mixed
     */
    public function getDescricao()
    {
        return $this->descricao;
    }

    /**
     * @param mixed $descricao
     */
    public function setDescricao($descricao)
    {
        if (!\Respect\Validation\Validator::stringType()->notEmpty()->length(1, 3000)->validate($descricao)) {
            throw new Exception("Descrição não pode estar vazia e não pode ter mais de 3000 caracteres.");
        }
        $this->descricao = $descricao;
    }

    /**
     * @return mixed
     */
    public function getTipo()
    {

        return $this->tipo;
    }

    /**
     * @param mixed $tipo
     */
    public function setTipo($tipo)
    {
        if (!\Respect\Validation\Validator::intType()->notEmpty()->length(1, 4)->validate($tipo)) {
            throw new Exception("Tipo de atividade inválido.");
        }
        $this->tipo = $tipo;
    }

    /**
     * @return mixed
     */
    public function getCapacidade()
    {
        return $this->capacidade;
    }

    /**
     * @param mixed $capacidade
     */
    public function setCapacidade($capacidade)
    {
        if (!\Respect\Validation\Validator::intType()->notEmpty()->length(0, null)->validate($capacidade)) {
            throw new Exception("Tipo de atividade inválido");
        }
        $this->capacidade = $capacidade;
    }

    /**
     * @return mixed
     */
    public function getDuracao()
    {
        return $this->duracao;
    }

    /**
     * @param mixed $duracao
     */
    public function setDuracao($duracao)
    {
        if (!\Respect\Validation\Validator::intType()->notEmpty()->length(0, null)->validate($duracao)) {
            throw new Exception("Duração da atividade inválida.");
        }
        $this->duracao = $duracao;
    }

    /**
     * @return mixed
     */
    public function getOrganizador()
    {
        return $this->organizador;
    }

    /**
     * @param mixed $organizador
     * @throws \Exception
     */
    public function setOrganizador($organizador)
    {
        if (!\Respect\Validation\Validator::email()->validate($organizador)) {
            throw new Exception("Email inválido.");
        }
        $handler = new \DatabaseHandler();
        if (!count($handler->getDataByEmail($organizador)) > 1) {
            throw new \Exception("Organizador não encontrado. Certifique-se que ele está registrado no sistema.");
        }
        $this->organizador = $organizador;
    }

    /**
     * @return array
     */
    public function getSessoes()
    {
        return $this->sessoes;
    }

    /**
     * @param array $sessoes
     */
    public function setSessoes($sessoes)
    {
        $this->sessoes = $sessoes;
    }

    /**
     * Atividade constructor.
     */
    public function __construct()
    {
        $sessoes = array();
    }

}