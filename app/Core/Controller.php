<?php
class Controller
{
    protected function view(string $view, array $data = [], bool $layout = true): void
    {
        extract($data, EXTR_SKIP);

        if ($layout) {
            require dirname(__DIR__) . '/Views/layouts/header.php';
        }

        require dirname(__DIR__) . '/Views/' . $view . '.php';

        if ($layout) {
            require dirname(__DIR__) . '/Views/layouts/footer.php';
        }
    }
}
