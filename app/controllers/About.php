<?php

class About extends Controller {
    public function index($nama = "raj", $perkerjaan = "dosen"){
        $data['judul'] = "Lunerburg & Co About";
        $data['nama'] = $nama;
        $data['perkerjaan'] = $perkerjaan;
        $this->view('templates/header', $data);
        $this->view('about/index', $data);
        $this->view('templates/footer');
    }
    public function page(){
        $this->view('about/page');
    }
}