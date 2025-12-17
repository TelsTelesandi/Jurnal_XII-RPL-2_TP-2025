<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\Notification;
use App\Models\Order;
use App\Models\RoutePlan;
use App\Models\RouteStop;
use App\Models\ProductReview;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DeliveryController extends Controller
{
    public function create(Order $order)
    {
        $user = auth()->user();
        if ($user->role === 'Driver' && (int)$order->assigned_driver_id !== (int)$user->id) {
            abort(403);
        }
        return view('deliveries.create', compact('order'));
    }

    public function store(Request $request, Order $order)
    {
        $user = auth()->user();
        if ($user->role === 'Driver' && (int)$order->assigned_driver_id !== (int)$user->id) {
            abort(403);
        }

        $data = $request->validate([
            'recipient' => ['required','string','max:255'],
            'signature' => ['nullable','file','max:4096'],
            'photo' => ['nullable','file','max:6144'],
            'notes' => ['nullable','string'],
            'driver_message' => ['nullable','string','max:1000'],
        ]);

        // Simpan file ke public/proofs agar tidak butuh storage:link
        $sigPath = null; $photoPath = null;
        $baseDir = public_path('proofs');
        if (!File::exists($baseDir)) { File::makeDirectory($baseDir, 0755, true); }

        $allowedExt = ['jpg','jpeg','png','gif','webp'];
        if ($request->hasFile('signature')) {
            $f = $request->file('signature');
            $ext = strtolower($f->getClientOriginalExtension());
            if (!in_array($ext, $allowedExt, true)) {
                return back()->withErrors(['signature' => 'File tanda tangan harus berupa gambar (jpg, jpeg, png, gif, webp).'])->withInput();
            }
            $sigPath = 'proofs/'.date('Ymd_His').'_' . Str::random(6) . '_ttd.' . $ext;
            $f->move(public_path('proofs'), basename($sigPath));
        }
        if ($request->hasFile('photo')) {
            $f = $request->file('photo');
            $ext = strtolower($f->getClientOriginalExtension());
            if (!in_array($ext, $allowedExt, true)) {
                return back()->withErrors(['photo' => 'File foto harus berupa gambar (jpg, jpeg, png, gif, webp).'])->withInput();
            }
            $photoPath = 'proofs/'.date('Ymd_His').'_' . Str::random(6) . '_foto.' . $ext;
            $f->move(public_path('proofs'), basename($photoPath));
        }

        $delivery = Delivery::updateOrCreate(
            ['order_id' => $order->id],
            [
                'delivered_at' => now(),
                'recipient' => $data['recipient'],
                'signature_path' => $sigPath,
                'photo_path' => $photoPath,
                'status' => 'delivered',
                'notes' => $data['notes'] ?? null,
                'driver_message' => $data['driver_message'] ?? null,
            ]
        );

        // Update status order saja (perencanaan/tracking dihapus)
        $order->update(['status' => 'delivered']);

        // Notify customer
        if ($order->customer_id) {
            Notification::create([
                'user_id' => $order->customer_id,
                'title' => 'Paket telah sampai - ' . $order->code,
                'body' => $delivery->driver_message ? ('Pesan dari driver: ' . $delivery->driver_message) : 'Paket Anda telah tiba. Mohon konfirmasi penerimaan.',
                'link_url' => route('deliveries.confirm.form', $order),
            ]);
        }

        return redirect()->route('orders.index')->with('success', 'Pengiriman dikonfirmasi dan notifikasi dikirim ke customer.');
    }

    public function confirmForm(Order $order)
    {
        $user = auth()->user();
        if ($user->id !== (int)$order->customer_id) {
            abort(403);
        }
        $delivery = Delivery::where('order_id', $order->id)->first();
        if (!$delivery || $delivery->status !== 'delivered') {
            return redirect()->route('orders.show', $order)->with('error', 'Driver belum menandai paket sebagai terkirim.');
        }
        return view('deliveries.confirm', compact('order','delivery'));
    }

    public function confirm(Request $request, Order $order)
    {
        $user = auth()->user();
        if ($user->id !== (int)$order->customer_id) {
            abort(403);
        }
        $data = $request->validate([
            'status' => ['required','in:approved,rejected'],
            'customer_notes' => ['nullable','string','max:1000'],
            'customer_quality' => ['nullable','string','max:50'],
            'customer_rating' => ['nullable','integer','min:1','max:5'],
        ]);
        $order->loadMissing('items');
        $delivery = Delivery::where('order_id', $order->id)->firstOrFail();
        $delivery->update([
            'customer_confirmed_at' => now(),
            'customer_confirmation_status' => $data['status'],
            'customer_notes' => $data['customer_notes'] ?? null,
            'customer_quality' => $data['customer_quality'] ?? null,
            'customer_rating' => $data['customer_rating'] ?? null,
        ]);

        // Create product reviews for each product in the order when rating provided (approved or rejected)
        if (!empty($data['customer_rating'])) {
            foreach ($order->items as $item) {
                ProductReview::updateOrCreate(
                    [
                        'product_id' => $item->product_id,
                        'order_id' => $order->id,
                        'user_id' => $user->id,
                    ],
                    [
                        'rating' => (int) $data['customer_rating'],
                        'quality' => $data['customer_quality'] ?? null,
                        'notes' => $data['customer_notes'] ?? null,
                    ]
                );
            }
        }

        // If customer rejects, restock and mark order as rejected so it won't appear in delivered reports
        if ($data['status'] === 'rejected') {
            foreach ($order->items as $item) {
                if ($item->product_id && $item->quantity) {
                    Product::where('id', $item->product_id)->increment('stock', (int) $item->quantity);
                }
            }
            $order->update(['status' => 'rejected']);
        }

        // Notify admins
        $admins = User::where('role','Admin')->get();
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'title' => 'Konfirmasi Customer - ' . $order->code,
                'body' => 'Customer ' . ($order->customer_name ?? '#'.$order->customer_id) . ' ' . ($data['status'] === 'approved' ? 'menerima' : 'menolak') . ' paket.',
                'link_url' => route('orders.show', $order),
            ]);
        }

        return redirect()->route('orders.show', $order)->with('success', 'Terima kasih! Konfirmasi Anda telah dikirim.');
    }
}
