<?php
class Router {
    public function dispatch() {
        $route = $_GET['r'] ?? 'home/index';
        [$controller, $action] = array_pad(explode('/', $route), 2, 'index');
        $class = ucfirst($controller) . 'Controller';
        $file = __DIR__ . '/../controllers/' . $class . '.php';
        if (!file_exists($file)) { http_response_code(404); echo 'Controller not found'; return; }
        require_once $file;
        $obj = new $class();
        if (!method_exists($obj, $action)) { http_response_code(404); echo 'Action not found'; return; }
        $obj->$action();
    }
}

