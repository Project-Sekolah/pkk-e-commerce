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
            ->withCount(['ratings as comments_count' => function ($query) {
                $query->whereNotNull('review_text')->where('review_text', '<>', '');
            }])
            ->withCount(['orderItems as sold_count' => function ($query) {
                $query->whereHas('order', fn ($order) => $order->whereIn('status', ['paid', 'shipped', 'completed']));
            }])
            ->where('is_active', true)
            ->whereHas('images')
            ->orderByDesc('sold_count')
            ->orderByDesc('ratings_avg_rating')
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