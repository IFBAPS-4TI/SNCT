<?php


namespace Models;


use Exception;

class Atividade
{
    private $nome;
    private $descricao;
    private $tipo;
    private $capacidade;
    private $duracao;
    private $organizador;
    private $sessoes = array();
    private $certificado;
    /**
     * @return mixed
     */
    public function getNome()
    {
        return $this->nome;
    }

    /**
     * @param mixed $nome
     * @throws Exception
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
     * @throws Exception
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
     * @throws Exception
     */
    public function setTipo($tipo)
    {
        if (!\Respect\Validation\Validator::intVal()->notEmpty()->min(1)->max(4)->validate($tipo)) {
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
     * @throws Exception
     */
    public function setCapacidade($capacidade)
    {
        if (!\Respect\Validation\Validator::intVal()->min(0)->validate($capacidade)) {
            throw new Exception("Capacidade da atividade inválida");
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
     * @throws Exception
     */
    public function setDuracao($duracao)
    {
        if (!\Respect\Validation\Validator::intVal()->notEmpty()->min(1)->validate($duracao)) {
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
        $this->sessoes = array();
        $this->certificado = false;
    }

    /**
     * @return bool
     */
    public function isCertificado()
    {
        return $this->certificado;
    }

    /**
     * @param bool $certificado
     */
    public function setCertificado($certificado)
    {
        $this->certificado = $certificado;
    }

}