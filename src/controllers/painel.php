<?php

class Painel
{
    protected $container;

    public function __construct(Slim\Container $container)
    {
        $this->container = $container;
    }

    public function home($request, $response, $args)
    {
        // your code
        // to access items in the container... $this->container->get('');
        return $this->container->view->render($response, 'panel/login.html', $args);;
    }

    public function contact($request, $response, $args)
    {
        // your code
        // to access items in the container... $this->container->get('');
        return $response;
    }
}


#return $this->view->render($response, 'panel/login.html', $args);