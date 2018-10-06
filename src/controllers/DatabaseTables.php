<?php


class DatabaseTables
{
    private $usuarios;
    private $administradores;
    private $atividades;
    private $monitores;
    private $sessoes;
    private $inscricoes;
    private $prefix;

    /**
     * DatabaseConfig constructor.
     */
    public function __construct()
    {
        if(getenv("sql_prefix")){
            $this->prefix = getenv("sql_prefix") . "_";
        }else{
            $this->prefix = "";
        }
        $this->usuarios = $this->prefix . "Usuario";
        $this->administradores = $this->prefix . "Administradores";
        $this->atividades = $this->prefix . "Atividade";
        $this->monitores = $this->prefix . "Monitor";
        $this->sessoes = $this->prefix . "Sessoes";
        $this->inscricoes = $this->prefix . "Inscricoes";
    }

    /**
     * @return string
     */
    public function getUsuarios()
    {
        return $this->usuarios;
    }

    /**
     * @param string $usuarios
     */
    public function setUsuarios($usuarios)
    {
        $this->usuarios = $usuarios;
    }

    /**
     * @return string
     */
    public function getAdministradores()
    {
        return $this->administradores;
    }

    /**
     * @param string $administradores
     */
    public function setAdministradores($administradores)
    {
        $this->administradores = $administradores;
    }

    /**
     * @return string
     */
    public function getAtividades()
    {
        return $this->atividades;
    }

    /**
     * @param string $atividades
     */
    public function setAtividades($atividades)
    {
        $this->atividades = $atividades;
    }

    /**
     * @return string
     */
    public function getMonitores()
    {
        return $this->monitores;
    }

    /**
     * @param string $monitores
     */
    public function setMonitores($monitores)
    {
        $this->monitores = $monitores;
    }

    /**
     * @return string
     */
    public function getSessoes()
    {
        return $this->sessoes;
    }

    /**
     * @param string $sessoes
     */
    public function setSessoes($sessoes)
    {
        $this->sessoes = $sessoes;
    }

    /**
     * @return string
     */
    public function getInscricoes()
    {
        return $this->inscricoes;
    }

    /**
     * @param string $inscricoes
     */
    public function setInscricoes($inscricoes)
    {
        $this->inscricoes = $inscricoes;
    }



}