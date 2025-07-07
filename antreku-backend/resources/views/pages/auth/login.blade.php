<?php
// File: resources/views/pages/auth/login.blade.php
?>
@extends('layouts.app')
@section('title', 'Login Pelanggan')

@section('content')
<div class="flex items-center justify-center mt-10">
    <div class="w-full max-w-md p-8 space-y-6 bg-white rounded-lg shadow-md">
        <h2 class="text-2xl font-bold text-center">Login Pelanggan</h2>
        
        @if(session('success'))
            <div class="p-3 text-sm text-green-700 bg-green-100 rounded-lg">{{ session('success') }}</div>
        @endif
        
        @if($errors->any())
            <div class="p-3 text-sm text-red-700 bg-red-100 rounded-lg">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST" class="space-y-6">
            @csrf
            <div>
                <label for="phone_number" class="text-sm font-medium">Nomor Telepon</label>
                <input name="phone_number" value="{{ old('phone_number') }}" type="tel" id="phone_number" class="w-full px-3 py-2 mt-1 border rounded-md" required>
            </div>
            <div>
                <label for="password" class="text-sm font-medium">Password</label>
                <input name="password" type="password" id="password" class="w-full px-3 py-2 mt-1 border rounded-md" required>
            </div>
            <button type="submit" class="w-full px-4 py-2 font-semibold text-white bg-blue-600 rounded-md hover:bg-blue-700">Login</button>
        </form>
        <p class="text-sm text-center">Belum punya akun? <a href="{{ route('register') }}" class="font-medium text-blue-600 hover:underline">Daftar di sini</a></p>
    </div>
</div>
@endsection