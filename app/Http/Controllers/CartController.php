<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        if (Auth::check() && auth()->user()->role !== 'Customer') {
            abort(403, 'Hanya Customer yang dapat mengakses keranjang belanja.');
        }
        $cart = $this->getOrCreateCart();
        $cartItems = $cart->items()->with('product')->get();
        
        return view('cart.index', compact('cart', 'cartItems'));
    }

    public function add(Request $request, Product $product)
    {
        if (Auth::check() && auth()->user()->role !== 'Customer') {
            abort(403, 'Hanya Customer yang dapat menambahkan ke keranjang.');
        }
        $request->validate([
            'quantity' => 'required|integer|min:1|max:' . $product->stock
        ]);

        $cart = $this->getOrCreateCart();
        
        // Check if item already exists in cart
        $cartItem = $cart->items()->where('product_id', $product->id)->first();
        
        if ($cartItem) {
            // Update quantity if item exists
            $newQuantity = $cartItem->quantity + $request->quantity;
            if ($newQuantity > $product->stock) {
                return back()->with('error', 'Jumlah melebihi stok yang tersedia!');
            }
            $cartItem->update(['quantity' => $newQuantity]);
        } else {
            // Create new cart item
            $cart->items()->create([
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'price' => $product->price
            ]);
        }

        return back()->with('success', 'Produk berhasil ditambahkan ke keranjang!');
    }

    public function update(Request $request, CartItem $cartItem)
    {
        if (Auth::check() && auth()->user()->role !== 'Customer') {
            abort(403, 'Hanya Customer yang dapat mengubah keranjang.');
        }
        $request->validate([
            'quantity' => 'required|integer|min:1|max:' . $cartItem->product->stock
        ]);

        $cartItem->update(['quantity' => $request->quantity]);

        return back()->with('success', 'Keranjang berhasil diperbarui!');
    }

    public function remove(CartItem $cartItem)
    {
        if (Auth::check() && auth()->user()->role !== 'Customer') {
            abort(403, 'Hanya Customer yang dapat mengubah keranjang.');
        }
        $cartItem->delete();
        
        return back()->with('success', 'Produk berhasil dihapus dari keranjang!');
    }

    public function clear()
    {
        if (Auth::check() && auth()->user()->role !== 'Customer') {
            abort(403, 'Hanya Customer yang dapat mengubah keranjang.');
        }
        $cart = $this->getOrCreateCart();
        $cart->items()->delete();
        
        return back()->with('success', 'Keranjang berhasil dikosongkan!');
    }

    public function count()
    {
        if (Auth::check() && auth()->user()->role !== 'Customer') {
            return response()->json(['count' => 0]);
        }
        $cart = $this->getOrCreateCart();
        return response()->json(['count' => $cart->total_items]);
    }

    private function getOrCreateCart()
    {
        if (Auth::check()) {
            return Cart::firstOrCreate(
                ['user_id' => Auth::id()],
                ['session_id' => null]
            );
        } else {
            $sessionId = session()->getId();
            return Cart::firstOrCreate(
                ['session_id' => $sessionId],
                ['user_id' => null]
            );
        }
    }
}
