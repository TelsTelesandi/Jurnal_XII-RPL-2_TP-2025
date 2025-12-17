@extends('layouts.app')

@section('content')
<div class="card" style="max-width:680px; margin:0 auto;">
    <h2 style="margin-top:0;">403 - Akses Ditolak</h2>
    <p>Anda tidak memiliki izin untuk mengakses halaman ini.</p>
    @auth
        <a href="{{ url()->previous() }}" class="btn">Kembali</a>
        <a href="/" class="btn">Beranda</a>
    @else
        <a href="{{ route('login') }}" class="btn">Login</a>
    @endauth
</div>
@endsection
