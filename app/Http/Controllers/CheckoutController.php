<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Silakan login sebagai Customer untuk melanjutkan checkout.');
        }
        if (auth()->user()->role !== 'Customer') {
            abort(403, 'Hanya Customer yang dapat melakukan checkout.');
        }
        $cart = $this->getCart();
        
        if (!$cart || $cart->items->count() === 0) {
            return redirect()->route('cart.index')->with('error', 'Keranjang kosong! Silakan tambahkan produk terlebih dahulu.');
        }

        $cartItems = $cart->items()->with('product')->get();
        
        return view('checkout.index', compact('cart', 'cartItems'));
    }

    public function store(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Silakan login sebagai Customer untuk melanjutkan checkout.');
        }
        if (auth()->user()->role !== 'Customer') {
            abort(403, 'Hanya Customer yang dapat melakukan checkout.');
        }
        $cart = $this->getCart();
        
        if (!$cart || $cart->items->count() === 0) {
            return redirect()->route('cart.index')->with('error', 'Keranjang kosong!');
        }

        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'shipping_address' => 'required|string',
            // maps removed: no coordinates required
            'payment_method' => 'required|in:cod',
            'notes' => 'nullable|string|max:1000'
        ]);

        $validated['payment_method'] = 'cod';

        DB::transaction(function () use ($validated, $cart) {
            // Generate order code
            $orderCode = 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(6));

            // Create order
            $order = Order::create([
                'customer_id' => Auth::id(),
                'code' => $orderCode,
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_email' => $validated['customer_email'],
                'shipping_address' => $validated['shipping_address'],
                'shipping_lat' => null,
                'shipping_lng' => null,
                'payment_method' => $validated['payment_method'],
                'payment_status' => 'pending',
                'total_amount' => $cart->total_price,
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
                // Set destination from shipping address
                'destination_name' => 'Customer Address',
                'destination_address' => $validated['shipping_address'],
                'destination_lat' => null,
                'destination_lng' => null,
                'origin_name' => null,
                'origin_address' => null,
                'origin_lat' => null,
                'origin_lng' => null,
            ]);

            // Create order items
            foreach ($cart->items as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'name' => $cartItem->product->name,
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->price,
                    'subtotal' => $cartItem->subtotal
                ]);

                // Update product stock
                $cartItem->product->decrement('stock', $cartItem->quantity);

                // Record stock movement (out)
                StockMovement::create([
                    'product_id' => $cartItem->product_id,
                    'type' => 'out',
                    'quantity' => $cartItem->quantity,
                    'ref_type' => 'order',
                    'ref_id' => $order->id,
                    'notes' => 'Order ' . $orderCode,
                    'created_by' => auth()->id(),
                ]);
            }

            // Clear cart after successful order
            $cart->items()->delete();
            $cart->delete();
        });

        return redirect()->route('checkout.success')->with('success', 'Pesanan berhasil dibuat! Menunggu validasi admin.');
    }

    public function success()
    {
        return view('checkout.success');
    }

    private function getCart()
    {
        if (Auth::check()) {
            return Cart::where('user_id', Auth::id())->with('items.product')->first();
        } else {
            $sessionId = session()->getId();
            return Cart::where('session_id', $sessionId)->with('items.product')->first();
        }
    }
}
