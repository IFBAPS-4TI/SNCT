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
$monitorOnly = function ($request, $response, $next) use ($app) {
    if ($this->session->exists('jwt_token')) {
        try {
            $token = (array) Util::decodeToken($this->session->get('jwt_token'));
            $handler = new DatabaseHandler();
            $user = $handler->TokenTranslation($token);
            if(!count($user->getMonitorias()) >= 1){
                throw new Exception("Monitor apenas.");
            }
        } catch (Exception $e) {
            Flash::message("<strong>Erro!</strong> Você não tem permissão para acessar esta área." . "{$e->getMessage()}", $type="error");
            return $response->withStatus(302)->withHeader('Location', $app->getContainer()->get('router')->pathFor('painel', []));
        }
    }else{
        Flash::message("<strong>Erro!</strong> Você não tem permissão para acessar esta área.", $type="error");
        return $response->withStatus(302)->withHeader('Location', $app->getContainer()->get('router')->pathFor('entrar', []));
    }
    return $next($request, $response);
};
$monitorOP = function ($request, $response, $next) use ($app) {
        try {
            $token = (array) Util::decodeToken($this->session->get('jwt_token'));
            $handler = new DatabaseHandler();
            $user = $handler->TokenTranslation($token);
            if(!count($user->getMonitorias()) >= 1){
                throw new Exception("Monitores apenas.");
            }else{
                $args = $request->getAttribute('routeInfo')[2];
                if (!in_array($args['id_atividade'], $user->getMonitorias())) {
                    throw new Exception("Monitor não tem acesso a essa atividade.");
                }
            }
        } catch (Exception $e) {
            Flash::message("<strong>Erro!</strong> {$e->getMessage()}", $type="error");
            return $response->withStatus(302)->withHeader('Location', $app->getContainer()->get('router')->pathFor('monitor.list', []));
        }
    return $next($request, $response);
};
$userdata = function ($request, $response, $next) use ($app) {
    if ($this->session->exists('jwt_token')) {
        try {
            $token = (array) Util::decodeToken($this->session->get('jwt_token'));
            $handler = new DatabaseHandler();
            $user = $handler->TokenTranslation($token);
            if($user->getisAdministrador()){
                $request = $request->withAttribute('isAdmin', $user->getisAdministrador());
            }
            if(count($user->getMonitorias()) >= 1){
                $request = $request->withAttribute('isMonitor', true);
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
    return $next($request, $response);
};