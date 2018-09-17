<?php
// DIC configuration
use Tamtamchik\SimpleFlash\TemplateFactory;
use Tamtamchik\SimpleFlash\Templates;

$container = $app->getContainer();

// Register component on container
$container['view'] = function ($container) {
    $settings = $container->get('settings')['renderer'];
    $view = new \Slim\Views\Twig($settings['template_path'], [
        'cache' => false # __DIR__ . '/../cache/'
    ]);

    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $container->get('request')->getUri()->getBasePath()), '/');
    $flash = new Twig_SimpleFunction('flash', function () {
        $template = TemplateFactory::create(Templates::BOOTSTRAP_4);
        $flash = new Tamtamchik\SimpleFlash\Flash($template);

        return $flash::display();
    });
    $view->addExtension(new Slim\Views\TwigExtension($container->get('router'), $basePath));
    $view->getEnvironment()->addFunction($flash);
    return $view;
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

// sessions
$container['session'] = function ($c) {
    return new \SlimSession\Helper;
};