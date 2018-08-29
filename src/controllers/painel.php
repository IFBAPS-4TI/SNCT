<?php

class Painel
{
    protected $view;

    public function __construct(\Slim\Views\Twig $view) {
        $this->view = $view;
    }
    public function login($request, $response, $args) {
        // your code here
        // use $this->view to render the HTML
        return $this->view->render($response, 'panel/login.html', $args);;
    }
}
