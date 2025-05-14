<?php

class Controller {
    protected $cloudinary;

    public function __construct() {
        global $cloudinary;
        $this->cloudinary = $cloudinary;
    }

    public function view($view, $data = []) {
        require_once '../app/views/' . $view . '.php';
    }

    public function model($model) {
        require_once '../app/models/' . $model . '.php';
        return new $model;
    }

    public function checkLogin() {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            header('Location: ' . BASEURL . '/user/login');
            exit;
        }
    }
}
