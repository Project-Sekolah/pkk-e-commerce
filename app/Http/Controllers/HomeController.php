<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Discount;
use App\Models\Product;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $categories = Category::where('is_active', true)->get();
        $discounts = Discount::with(['products' => fn ($query) => $query->where('products.is_active', true)->with('images')])
            ->where('is_active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->whereHas('products', fn ($query) => $query->where('products.is_active', true))
            ->orderByDesc('percentage')
            ->take(5)
            ->get();

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
            'discounts' => $discounts,
            'products' => $products,
        ]);
    }
}