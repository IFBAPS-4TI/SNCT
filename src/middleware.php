<?php
// Application middleware

// e.g: $app->add(new \Slim\Csrf\Guard);
use Tamtamchik\SimpleFlash\Flash;
$settings = $container->get('settings');
/*
 * SlimSession
 */
$app->add(new \Slim\Middleware\Session([
    'name' => $settings['sessions']['name'],
    'autorefresh' => $settings['sessions']['autorefresh'],
    'lifetime' => $settings['sessions']['lifetime']
]));

$adminOnly = function ($request, $response, $next) use ($app) {
    if ($this->session->exists('jwt_token')) {
        try {
            $token = (array) Util::decodeToken($this->session->get('jwt_token'));
            $handler = new DatabaseHandler();
            $user = $handler->TokenTranslation($token);
            if($user->getisAdministrador()){
                return $next($request, $response);
            }else{
                Flash::message("<strong>Erro!</strong> Você não tem permissão para acessar esta área.", $type="error");
                return $response->withStatus(302)->withHeader('Location', $app->getContainer()->get('router')->pathFor('painel', []));
            }

        } catch (Exception $e) {
            $this->session->delete('jwt_token');
            Flash::message("<strong>Erro!</strong> Você não tem permissão para acessar esta área." . "{$e->getMessage()}", $type="error");
            return $response->withStatus(302)->withHeader('Location', $app->getContainer()->get('router')->pathFor('entrar', []));
        }
    }else{
        Flash::message("<strong>Erro!</strong> Você não tem permissão para acessar esta área.", $type="error");
        return $response->withStatus(302)->withHeader('Location', $app->getContainer()->get('router')->pathFor('entrar', []));
    }
};