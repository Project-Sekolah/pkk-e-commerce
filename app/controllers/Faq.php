<?php

class Faq extends Controller {

    public function index() {
        // Default view load

        $data['judul'] = 'Lunerburg & Co';
        $this->render(['faq/index'], $data);

    }

}
