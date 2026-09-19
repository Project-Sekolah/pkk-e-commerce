<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $categories = Category::where('is_active', true)->get();

        $products = Product::with(['category', 'images', 'user', 'ratings.user'])
            ->withAvg('ratings', 'rating')
            ->withCount('ratings')
            ->where('is_active', true)
            ->latest()
            ->take(8)
            ->get();

        return view('home', [
            'judul' => 'Lunerburg & Co - Home',
            'categories' => $categories,
            'products' => $products,
        ]);
    }
}