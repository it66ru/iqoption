<?php

require_once('config.php');


// автозагрузка классов
spl_autoload_register(function ($className) {
    $fileName = __DIR__ . '/app/' . str_replace('\\', '/', $className) . '.php';
    $fileName = strtolower($fileName);
    if (file_exists($fileName)) {
        require_once($fileName);
    }
});


// роутинг
$_SERVER['REDIRECT_URL'] = strpos($_SERVER['REQUEST_URI'], '?') ? strstr($_SERVER['REQUEST_URI'], '?', true) : $_SERVER['REQUEST_URI'];
$request_uri = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REDIRECT_URL'] : $_SERVER['argv'][1];
$url = explode('/', trim($request_uri, '/'));
$cName = 'Controllers\\' . ($url[0] ? array_shift($url) : 'main');

if (class_exists($cName)) {

    if (get_parent_class($cName) == 'Core\REST') {
        // REST-контроллер

        $id = isset($url[0]) ? $url[0] : null;
        $c = new $cName;

        $c->_input = json_decode(file_get_contents('php://input'), true);
        $c->_request = $_REQUEST;

        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            if ($id) {
                $c->get($id);
            } else {
                $c->main();
            }
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $c->post();
        }
        if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
            $c->put($id);
        }
        if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
            $c->delete($id);
        }

    } else {
        // Обычный контроллер
        $mName = !empty($url[0]) ? array_shift($url) : 'index';
        $c = new $cName;
        if (in_array($mName, get_class_methods($c))) {
            call_user_func_array(array($c, $mName), $url);
        } else {
            \Core\Sys::show_404();
        }
    }
} else {
    \Core\Sys::show_404();
}