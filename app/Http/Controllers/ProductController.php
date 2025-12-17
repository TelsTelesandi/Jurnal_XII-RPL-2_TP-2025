<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductReview;
use App\Models\OrderItem;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::active();
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by brand
        if ($request->filled('brand')) {
            $query->where('brand', $request->brand);
        }

        // Filter by size
        if ($request->filled('size')) {
            $query->where('size', $request->size);
        }

        // Sort by price
        if ($request->filled('sort')) {
            if ($request->sort === 'price_asc') {
                $query->orderBy('price', 'asc');
            } elseif ($request->sort === 'price_desc') {
                $query->orderBy('price', 'desc');
            } elseif ($request->sort === 'name') {
                $query->orderBy('name', 'asc');
            }
        } else {
            $query->orderBy('brand')->orderBy('price');
        }

        $products = $query->paginate(12);
        $brands = Product::active()->distinct()->pluck('brand');
        $sizes = Product::active()->distinct()->pluck('size');

        return view('products.index', compact('products', 'brands', 'sizes'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        // Load reviews and metrics
        $reviewsQuery = ProductReview::with('user')
            ->where('product_id', $product->id)
            ->latest();
        $reviews = $reviewsQuery->limit(20)->get();
        $avgRating = round((float) ProductReview::where('product_id', $product->id)->avg('rating'), 2);
        $reviewsCount = (int) ProductReview::where('product_id', $product->id)->count();

        // Purchase stats (delivered orders only)
        $purchasesCount = (int) OrderItem::where('product_id', $product->id)
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'delivered')
            ->sum('order_items.quantity');
        $buyersCount = (int) OrderItem::where('product_id', $product->id)
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'delivered')
            ->distinct('orders.customer_id')
            ->count('orders.customer_id');

        return view('products.show', compact('product','reviews','avgRating','reviewsCount','purchasesCount','buyersCount'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('products.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
            'description' => 'nullable|string',
            'size' => 'required|string|max:50',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image_url' => 'nullable|url',
        ]);

        Product::create($validated);

        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
            'description' => 'nullable|string',
            'size' => 'required|string|max:50',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image_url' => 'nullable|url',
            'is_active' => 'boolean',
        ]);

        $product->update($validated);

        return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus!');
    }

    public function stockIn(Request $request, Product $product)
    {
        if (auth()->user()->role !== 'Admin') { abort(403); }
        $data = $request->validate([
            'quantity' => ['required','integer','min:1'],
            'notes' => ['nullable','string','max:255'],
        ]);
        $product->increment('stock', $data['quantity']);
        StockMovement::create([
            'product_id' => $product->id,
            'type' => 'in',
            'quantity' => $data['quantity'],
            'ref_type' => 'manual',
            'ref_id' => null,
            'notes' => $data['notes'] ?? 'Stock in by admin',
            'created_by' => auth()->id(),
        ]);
        return back()->with('success', 'Stok berhasil ditambahkan.');
    }
}
