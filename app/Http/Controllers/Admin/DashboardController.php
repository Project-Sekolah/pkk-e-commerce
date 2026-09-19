<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $totalProducts = Product::where('is_active', true)->count();
        $totalOrders = Order::count();
        $totalRevenue = Order::whereIn('status', ['paid', 'completed', 'shipped'])->sum('total');

        $userTypeCounts = [
            'buyer' => User::where('role', 'buyer')->count(),
            'seller' => User::where('role', 'seller')->count(),
            'admin' => User::where('role', 'admin')->count(),
        ];

        // Monthly sales for the current year (database agnostic)
        $monthExpr = DB::getDriverName() === 'sqlite'
            ? "CAST(strftime('%m', created_at) AS INTEGER)"
            : "MONTH(created_at)";

        $monthlySales = Order::select(
            DB::raw("{$monthExpr} as month"),
            DB::raw('SUM(total) as revenue')
        )
            ->whereIn('status', ['paid', 'completed', 'shipped'])
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->pluck('revenue', 'month')
            ->toArray();

        $monthlySalesData = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthlySalesData[$m] = (float) ($monthlySales[$m] ?? 0);
        }

        // Top 5 selling products
        $topProducts = Product::select('products.id', 'products.title', 'users.full_name as seller_name', 'categories.name as category', DB::raw('SUM(order_items.quantity) as sold'))
            ->join('order_items', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('users', 'products.user_id', '=', 'users.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereNull('products.deleted_at')
            ->groupBy('products.id', 'products.title', 'users.full_name', 'categories.name')
            ->orderByDesc('sold')
            ->take(5)
            ->get();

        $categories = Category::all();

        return view('admin.dashboard', [
            'judul' => 'Dashboard Admin - Lunerburg & Co',
            'user' => Auth::user(),
            'total_users' => $totalUsers,
            'total_products' => $totalProducts,
            'total_orders' => $totalOrders,
            'total_revenue' => (float) $totalRevenue,
            'user_type_counts' => $userTypeCounts,
            'monthly_sales' => array_values($monthlySalesData),
            'top_products' => $topProducts,
            'categories' => $categories,
        ]);
    }
}