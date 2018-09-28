<?php


namespace Models;


class Sessao
{
    private $data;
    private $hora;

    /**
     * Sessao constructor.
     * @param $data
     * @param $hora
     */
    public function __construct($data, $hora)
    {
        $this->data = $data;
        $this->hora = $hora;
    }

    public function buildTimestamp(){
        return $this->data . "U" . $this->hora;
    }
}