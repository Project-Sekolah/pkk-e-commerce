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
            header('Location: ' . BASEURL . '/');
            exit;
        }
    }

    protected function attachUserData(&$data) {
        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
            $userModel = $this->model('User_model');
            $data['user'] = $userModel->getUserById($_SESSION['user']['id']);
            $data['addresses'] = $userModel->getAddressesByUserId($_SESSION['user']['id']);
        }
    }

    protected function render($views = [], $data = []) {
        $this->attachUserData($data);
        
        $this->view('templates/modal', $data);
        $this->view('templates/header', $data);
        $this->view('templates/sidebar', $data);
        foreach ($views as $view) {
            $this->view($view, $data);
        }
        $this->view('templates/footer');
    }
}
