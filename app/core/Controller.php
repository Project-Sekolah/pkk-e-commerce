<?php

class Controller
{
  protected $cloudinary;

  public function __construct()
  {
    if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
    global $cloudinary;
    $this->cloudinary = $cloudinary;
  }

  public function view($view, $data = [])
  {
    require_once "../app/views/" . $view . ".php";
  }

  public function model($model)
  {
    require_once "../app/models/" . $model . ".php";
    return new $model();
  }

  public function checkAdmin()
{
    // Cek apakah user sudah login dan merupakan admin
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        header('Location: ' . BASEURL . '/');
        exit;
    }
}


  public function checkLogin()
  {
    if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
      header("Location: " . BASEURL . "/");
      exit();
    }
  }

  protected function attachUserData(&$data)
  {
    if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true) {
      $userModel = $this->model("User_model");
      $data["user"] = $userModel->getUserById($_SESSION["user"]["id"]);
      $data["addresses"] = $userModel->getAddressesByUserId(
        $_SESSION["user"]["id"]
      );
    }
  }

  protected function render($views = [], $data = [])
  {
    $this->attachUserData($data);

    $this->view("templates/modal", $data);
    $this->view("templates/header", $data);
    $this->view("templates/sidebar", $data);
    foreach ($views as $view) {
      $this->view($view, $data);
    }
    $this->view("templates/footer");
  }

    protected function renderAdmin($views = [], $data = [])
  {
    $this->attachUserData($data);

    $this->view("admin/templates/header", $data);
    $this->view("admin/templates/sidebar", $data);
    foreach ($views as $view) {
      $this->view($view, $data);
    }
    $this->view("admin/templates/footer");
  }
}
