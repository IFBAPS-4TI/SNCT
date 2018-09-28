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
     */
    public function setOrganizador($organizador)
    {
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