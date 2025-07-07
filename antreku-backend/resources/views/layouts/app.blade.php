<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'AntreKu')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
    <!-- Midtrans Snap.js -->
    <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
</head>
<body class="bg-gray-50">
    <header class="bg-white shadow-sm">
        <nav class="container mx-auto px-6 py-3 flex justify-between items-center">
            <a href="{{ route('home') }}" class="text-2xl font-bold text-blue-600">AntreKu</a>
            <div>
                @auth('customer')
                    <a href="{{ route('history') }}" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-100">Riwayat</a>
                    <span class="mx-2 text-gray-500">|</span>
                    <span class="mr-4 text-sm">Halo, {{ Auth::guard('customer')->user()->name }}</span>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-3 py-2 rounded-md text-sm font-medium bg-red-500 text-white hover:bg-red-600">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-100">Login</a>
                    <a href="{{ route('register') }}" class="ml-2 px-4 py-2 rounded-md text-sm font-medium bg-blue-600 text-white hover:bg-blue-700">Daftar</a>
                @endauth
            </div>
        </nav>
    </header>
    <main class="container mx-auto p-6">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>