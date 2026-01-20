<?php
class Controller {
    protected function render($view, $params = []) {
        extract($params);
        $viewFile = __DIR__ . '/../views/' . $view . '.php';
        if (!file_exists($viewFile)) { http_response_code(404); echo 'View not found'; return; }
        include __DIR__ . '/../views/layouts/main.php';
    }
}

