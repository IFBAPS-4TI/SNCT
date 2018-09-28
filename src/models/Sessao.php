<?php


namespace Models;


use Exception;

class Sessao
{
    private $data;
    private $hora;

    /**
     * Sessao constructor.
     * @param $data
     * @param $hora
     * @throws Exception
     */
    public function __construct($data, $hora)
    {
        if (!\Respect\Validation\Validator::date('d/m/Y')->notEmpty()->validate($data)) {
            die(var_dump($data));
            throw new Exception("Data inválida.");
        }
        if (!\Respect\Validation\Validator::stringType()->notEmpty()->length(5, 5)->validate($hora)) {
            throw new Exception("Horá inválida, ela deve vir no formato 24 horas.");
        }
        $this->data = $data;
        $this->hora = $hora;
    }

    public function buildTimestamp(){
        return $this->data . "U" . $this->hora;
    }
}