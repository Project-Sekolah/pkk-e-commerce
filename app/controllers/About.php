<?php

class About extends Controller {
    public function index($nama = "raj", $perkerjaan = "dosen"){

        $data = [
            'judul' => 'Lunerburg & Co About',
            'nama' => $nama,
            'perkerjaan' => $perkerjaan
        ];
        $this->render(['about/index'], $data);

    }
    public function page(){
        $this->view('about/page');
    }
}