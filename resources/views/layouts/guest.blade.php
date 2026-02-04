<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex bg-gray-50 dark:bg-gray-900">
            <!-- Left Side: Branding (Desktop) -->
            <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-blue-600 to-indigo-900 items-center justify-center relative overflow-hidden">
                <!-- Decorative Circles (RND?) -->
                <div class="absolute w-96 h-96 bg-white/10 rounded-full -top-20 -left-20 blur-3xl"></div>
                <div class="absolute w-96 h-96 bg-white/10 rounded-full -bottom-20 -right-20 blur-3xl"></div>
                
                <div class="relative z-10 p-12 text-center w-full max-w-2xl px-8">
                    @php
                        $vendor = app()->has('current_vendor') ? app('current_vendor') : null;
                    @endphp

                    @if($vendor && $vendor->logo)
                        <img src="{{ asset('storage/' . $vendor->logo) }}" alt="{{ $vendor->company_name }}" class="w-full max-w-xs h-auto mx-auto object-contain drop-shadow-lg bg-white p-4 rounded-xl">
                        <h2 class="mt-8 text-3xl font-bold text-white tracking-widest uppercase">{{ $vendor->company_name }}</h2>
                    @else
                        <!-- Constrain Width to prevent cutting -->
                        <x-application-logo class="w-full max-w-md h-auto mx-auto object-contain drop-shadow-lg" />
                        <h2 class="mt-8 text-3xl font-bold text-white tracking-widest uppercase">Analogue</h2>
                        <p class="text-blue-100 mt-2 text-lg font-light tracking-wide">IT Solutions</p>
                    @endif
                </div>
            </div>

            <!-- Right Side: Form -->
            <div class="w-full lg:w-1/2 flex items-center justify-center p-8">
                <div class="w-full max-w-md bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-xl ring-1 ring-gray-900/5 dark:ring-white/10">
                    <!-- Mobile Logo -->
                    <div class="lg:hidden flex justify-center mb-6">
                        <x-application-logo class="h-16 w-auto" />
                    </div>

                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
