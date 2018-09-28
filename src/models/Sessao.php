<?php


namespace Models;


use Exception;

class Sessao
{
    private $data;
    private $hora;
    private $local;

    /**
     * Sessao constructor.
     * @param $data
     * @param $hora
     * @param $local
     * @throws Exception
     */
    public function __construct($data, $hora, $local)
    {
        if (!\Respect\Validation\Validator::date('d/m/Y')->notEmpty()->validate($data)) {
            throw new Exception("Data inválida.");
        }
        if (!\Respect\Validation\Validator::stringType()->notEmpty()->length(5, 5)->validate($hora)) {
            throw new Exception("Horá inválida, ela deve vir no formato 24 horas.");
        }
        if (!\Respect\Validation\Validator::stringType()->notEmpty()->validate($local)) {
            throw new Exception("Local inválido.");
        }
        $this->data = $data;
        $this->hora = $hora;
        $this->local = $local;
    }

    /**
     * @return mixed
     */
    public function getLocal()
    {
        return $this->local;
    }


    public function buildTimestamp()
    {
        return $this->data . "U" . $this->hora;
    }
}