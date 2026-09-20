<?php

namespace App\Http\Controllers;

class PageController extends Controller
{
    public function about()
    {
        return view('pages.about', [
            'judul' => 'About Us - Lunerburg & Co',
        ]);
    }

    public function faq()
    {
        return view('pages.faq', [
            'judul' => 'FAQ - Lunerburg & Co',
        ]);
    }
}