@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200">
            <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                ✅ Konfirmasi Penerimaan Paket
            </h1>
            <p class="mt-1 text-sm text-gray-600">Order {{ $order->code }}</p>
        </div>

        <div class="px-6 py-6 space-y-6">
            @if($delivery->driver_message)
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-blue-800">
                    <div class="font-semibold mb-1">Pesan dari Driver:</div>
                    <div class="text-sm">{{ $delivery->driver_message }}</div>
                </div>
            @endif

            <form method="POST" action="{{ route('deliveries.confirm', $order) }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Anda</label>
                    <div class="flex items-center gap-4">
                        <label class="inline-flex items-center">
                            <input type="radio" name="status" value="approved" required class="h-4 w-4 text-green-600" />
                            <span class="ml-2 text-sm text-gray-700">Paket diterima</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="status" value="rejected" class="h-4 w-4 text-red-600" />
                            <span class="ml-2 text-sm text-gray-700">Tolak (ada masalah)</span>
                        </label>
                    </div>
                    @error('status')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Catatan untuk Admin (opsional)</label>
                    <textarea name="customer_notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Tuliskan masukan atau kendala...">{{ old('customer_notes') }}</textarea>
                    @error('customer_notes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kualitas Barang</label>
                    <select name="customer_quality" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Pilih kualitas (opsional)</option>
                        @php($qualities = ['Sangat Baik','Baik','Cukup','Buruk'])
                        @foreach($qualities as $q)
                            <option value="{{ $q }}" {{ old('customer_quality') === $q ? 'selected' : '' }}>{{ $q }}</option>
                        @endforeach
                    </select>
                    @error('customer_quality')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
                    <div class="flex items-center gap-2">
                        @for($i=1;$i<=5;$i++)
                            <label class="cursor-pointer">
                                <input type="radio" name="customer_rating" value="{{ $i }}" class="hidden" {{ (int)old('customer_rating') === $i ? 'checked' : '' }}>
                                <span class="star text-2xl">☆</span>
                            </label>
                        @endfor
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Pilih 1-5 bintang (opsional)</p>
                    @error('customer_rating')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors">Kirim Konfirmasi</button>
                    <a href="{{ route('orders.show', $order) }}" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md font-medium transition-colors text-center">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    const labels = document.querySelectorAll('label .star');
    function paint(value){
        labels.forEach((el, idx)=>{
            el.textContent = (idx < value) ? '★' : '☆';
            el.style.color = '#f59e0b';
        });
    }
    labels.forEach((el, idx)=>{
        el.addEventListener('click', ()=>{
            const input = el.previousElementSibling; // radio
            input.checked = true;
            paint(idx+1);
        });
    });
    const checked = document.querySelector('input[name="customer_rating"]:checked');
    paint(checked ? parseInt(checked.value) : 0);
});
</script>
@endpush
