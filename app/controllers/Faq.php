<?php

class Faq extends Controller {

    public function index() {
        // Default view load
        $data['judul'] = 'Lunerburg & Co';
        $this->view('templates/header', $data);
        $this->view('faq/index', $data);
        $this->view('templates/footer');
    }

}
