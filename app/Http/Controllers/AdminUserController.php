<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    public function index()
    {
        if (auth()->user()->role !== 'Admin') { abort(403); }

        $admins = User::where('role','Admin')->orderBy('name')->get();
        $customers = User::where('role','Customer')->orderBy('name')->paginate(20);
        $drivers = User::where('role','Driver')->orderBy('name')->get();

        $driverIds = $drivers->pluck('id');
        $stats = Order::select('assigned_driver_id',
                DB::raw("sum(case when status='assigned' then 1 else 0 end) as assigned_count"),
                DB::raw("sum(case when status='in_transit' then 1 else 0 end) as in_transit_count"),
                DB::raw("sum(case when status='delivered' then 1 else 0 end) as delivered_count")
            )
            ->whereIn('assigned_driver_id', $driverIds)
            ->groupBy('assigned_driver_id')
            ->get()
            ->keyBy('assigned_driver_id');

        $driverSummaries = $drivers->map(function($d) use ($stats) {
            $s = $stats->get($d->id);
            $assigned = (int)($s->assigned_count ?? 0);
            $inTransit = (int)($s->in_transit_count ?? 0);
            $delivered = (int)($s->delivered_count ?? 0);
            $status = $inTransit > 0 ? 'Dalam Perjalanan' : ($assigned > 0 ? 'Menunggu Konfirmasi / Mulai' : 'Idle');
            return compact('d','assigned','inTransit','delivered','status');
        });

        return view('admin.users.index', [
            'admins' => $admins,
            'customers' => $customers,
            'driverSummaries' => $driverSummaries,
        ]);
    }

    public function create()
    {
        if (auth()->user()->role !== 'Admin') { abort(403); }
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== 'Admin') { abort(403); }
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255','unique:users,email'],
            'password' => ['required','string','min:6','confirmed'],
            'role' => ['required','in:Admin,Driver'],
            'phone' => ['nullable','string','max:30'],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'phone' => $data['phone'] ?? null,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Akun berhasil dibuat.');
    }
}
