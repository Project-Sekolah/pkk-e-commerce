<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Discount;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductRating;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    private int $perPage = 12;

    public function index(Request $request)
    {
        $categories = Category::where('is_active', true)->get();

        $categoryParam = $request->input('categories', $request->input('category'));
        $categorySlugs = is_array($categoryParam) ? $categoryParam : ($categoryParam ? explode(',', $categoryParam) : []);
        $genders = (array) $request->input('gender', []);
        $search = $request->input('search', '');

        $query = Product::with(['category', 'images', 'user', 'ratings.user'])
            ->withAvg('ratings', 'rating')
            ->withCount('ratings')
            ->where('is_active', true);

        if (!empty($categorySlugs)) {
            $query->whereHas('category', function ($q) use ($categorySlugs) {
                $q->whereIn('slug', $categorySlugs);
            });
        }

        if (!empty($genders)) {
            $query->whereIn('gender', $genders);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $products = $query->latest()->paginate($this->perPage)->withQueryString();

        return view('product.index', [
            'judul' => 'Produk - Lunerburg & Co',
            'categories' => $categories,
            'products' => $products,
            'selectedCategories' => $categorySlugs,
            'selectedGenders' => $genders,
            'search' => $search,
        ]);
    }

    public function show($id)
    {
        $product = Product::with(['category', 'images', 'user', 'ratings.user'])
            ->withAvg('ratings', 'rating')
            ->withCount('ratings')
            ->findOrFail($id);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'id' => $product->id,
                'title' => $product->title,
                'slug' => $product->slug,
                'price' => (float) $product->price,
                'stock' => $product->stock,
                'description' => $product->description,
                'gender' => $product->gender,
                'category_name' => $product->category?->name,
                'owner_name' => $product->user?->full_name ?? $product->user?->username,
                'owner_phone' => $product->user?->phone_number,
                'images' => $product->images->pluck('image_url'),
                'average_rating' => round((float) $product->ratings_avg_rating, 1),
                'total_ratings' => $product->ratings_count,
                'reviews' => $product->ratings->map(fn($r) => [
                    'username' => $r->user?->full_name ?? $r->user?->username ?? 'Anonymous',
                    'user_image' => $r->user?->image ?? asset('assets/img/default-avatar.jpg'),
                    'rating' => $r->rating,
                    'review_text' => $r->review_text,
                    'created_at' => $r->created_at->format('d M Y'),
                ]),
            ]);
        }

        return view('product.detail', [
            'judul' => $product->title . ' - Detail Produk',
            'product' => $product,
        ]);
    }

    public function addRating(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'review_text' => ['nullable', 'string', 'max:1000'],
        ]);

        ProductRating::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'product_id' => $validated['product_id'],
            ],
            [
                'rating' => $validated['rating'],
                'review_text' => $validated['review_text'] ?? null,
            ]
        );

        return back()->with('alert', [
            'type' => 'success',
            'message' => 'Rating berhasil dikirim! Terima kasih atas ulasannya.',
        ]);
    }

    public function seller()
    {
        $products = Product::with(['category', 'images'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate($this->perPage);

        return view('product.seller', [
            'judul' => 'Produk Saya - Lunerburg & Co',
            'products' => $products,
        ]);
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->get();

        return view('product.add', [
            'judul' => 'Tambah Produk Baru',
            'categories' => $categories,
        ]);
    }

    public function store(Request $request, CloudinaryService $cloudinary)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'description' => ['required', 'string'],
            'gender' => ['required', 'in:pria,wanita,all'],
            'images.*' => ['nullable', 'image', 'max:5120'],
        ]);

        $baseSlug = Str::slug($validated['title']);
        $slug = $baseSlug;
        $count = 1;
        while (Product::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $count++;
        }

        $product = Product::create([
            'category_id' => $validated['category_id'],
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'slug' => $slug,
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'description' => $validated['description'],
            'gender' => $validated['gender'],
            'is_active' => true,
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $imageUrl = $cloudinary->upload($file, 'product_images');
                if ($imageUrl) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_url' => $imageUrl,
                    ]);
                }
            }
        }

        return redirect()->route('products.seller')->with('alert', [
            'type' => 'success',
            'message' => 'Produk berhasil ditambahkan.',
        ]);
    }

    public function edit($id)
    {
        $product = Product::with(['images', 'discounts'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        $categories = Category::where('is_active', true)->get();
        $discounts = Discount::where('user_id', Auth::id())->where('is_active', true)->get();

        return view('product.edit', [
            'judul' => 'Edit Produk: ' . $product->title,
            'product' => $product,
            'categories' => $categories,
            'discounts' => $discounts,
            'product_images' => $product->images,
        ]);
    }

    public function update(Request $request, $id, CloudinaryService $cloudinary)
    {
        $product = Product::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'description' => ['required', 'string'],
            'gender' => ['required', 'in:pria,wanita,all'],
            'discount_id' => ['nullable', 'exists:discounts,id'],
            'images.*' => ['nullable', 'image', 'max:5120'],
        ]);

        if ($validated['title'] !== $product->title) {
            $baseSlug = Str::slug($validated['title']);
            $slug = $baseSlug;
            $count = 1;
            while (Product::where('slug', $slug)->where('id', '!=', $product->id)->exists()) {
                $slug = $baseSlug . '-' . $count++;
            }
            $product->slug = $slug;
        }

        $product->update([
            'category_id' => $validated['category_id'],
            'title' => $validated['title'],
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'description' => $validated['description'],
            'gender' => $validated['gender'],
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $imageUrl = $cloudinary->upload($file, 'product_images');
                if ($imageUrl) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_url' => $imageUrl,
                    ]);
                }
            }
        }

        if (!empty($validated['discount_id'])) {
            $discount = Discount::findOrFail($validated['discount_id']);
            $product->discounts()->syncWithoutDetaching([
                $discount->id => [
                    'id' => (string) Str::uuid(),
                    'start_date' => $discount->start_date,
                    'end_date' => $discount->end_date,
                    'is_active' => true,
                ],
            ]);
        }

        return redirect()->route('products.seller')->with('alert', [
            'type' => 'success',
            'message' => 'Produk berhasil diperbarui.',
        ]);
    }

    public function destroy($id)
    {
        $product = Product::where('user_id', Auth::id())
            ->orWhere(fn($q) => Auth::user()->isAdmin() ? $q : $q->whereNull('id'))
            ->findOrFail($id);

        CartItem::where('product_id', $product->id)->delete();
        $product->delete();

        return back()->with('alert', [
            'type' => 'success',
            'message' => 'Produk berhasil dihapus.',
        ]);
    }

    public function deleteImage($id)
    {
        $image = ProductImage::whereHas('product', fn($q) => $q->where('user_id', Auth::id()))->findOrFail($id);
        $image->delete();

        return back()->with('alert', [
            'type' => 'success',
            'message' => 'Gambar berhasil dihapus.',
        ]);
    }
}