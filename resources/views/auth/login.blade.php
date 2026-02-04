<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Laravel') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-white dark:bg-gray-900">
        <div class="min-h-screen flex">
            <!-- Left Side: Branding -->
            <div class="hidden lg:flex lg:w-1/2 bg-[#051643] relative overflow-hidden items-center justify-center">
                <!-- Premium Gradient & Texture -->
                <div class="absolute inset-0 bg-gradient-to-br from-[#051643] via-[#0b2b6d] to-[#051643] opacity-100"></div>
                <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20 brightness-100 contrast-150 mix-blend-overlay"></div>
                
                <!-- Abstract Shapes -->
                <div class="absolute -top-24 -left-24 w-96 h-96 bg-white/10 rounded-full blur-3xl mix-blend-overlay"></div>
                <div class="absolute bottom-0 right-0 w-[500px] h-[500px] bg-indigo-500/30 rounded-full blur-3xl mix-blend-overlay"></div>
                
                <!-- Logo Content -->
                <div class="relative z-10 p-12 text-center">
                    <div class="flex flex-col items-center justify-center">
                         <!-- Logo with White Background -->
                         <div class="bg-white p-6 rounded-2xl shadow-2xl mb-6">
                            <img src="{{ \App\Models\GlobalSetting::where('key', 'logo')->value('value') }}" 
                                 alt="Logo" 
                                 class="w-auto h-24 object-contain">
                         </div>
                         
                        
                    </div>
                </div>
            </div>

            <!-- Right Side: Login Form -->
            <div class="w-full lg:w-1/2 flex items-center justify-center p-8 lg:p-12 bg-white dark:bg-gray-900">
                <div class="w-full max-w-[420px] space-y-8">
                    <div class="text-center lg:text-left">
                        <h2 class="text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white">Welcome back</h2>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Enter your credentials to access your account</p>
                    </div>

                    <!-- Session Status -->
                    <x-auth-session-status class="mb-4" :status="session('status')" />

                    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-6">
                        @csrf
                        
                        <div class="space-y-5">
                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Email address</label>
                                <div class="relative">
                                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" 
                                        class="block w-full px-4 py-3.5 rounded-xl text-gray-900 bg-gray-50 border border-gray-200 focus:bg-white focus:border-[#051643] focus:ring-2 focus:ring-[#051643]/20 transition-all duration-200 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:focus:border-[#051643] placeholder-gray-400 sm:text-sm"
                                        placeholder="admin@example.com">
                                </div>
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>

                            <!-- Password -->
                            <div x-data="{ show: false }">
                                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Password</label>
                                <div class="relative">
                                    <input id="password" ::type="show ? 'text' : 'password'" name="password" required autocomplete="current-password"
                                        class="block w-full px-4 py-3.5 pr-12 rounded-xl text-gray-900 bg-gray-50 border border-gray-200 focus:bg-white focus:border-[#051643] focus:ring-2 focus:ring-[#051643]/20 transition-all duration-200 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:focus:border-[#051643] placeholder-gray-400 sm:text-sm"
                                        placeholder="••••••••">
                                    <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400 hover:text-gray-600 transition-colors">
                                        <svg x-show="!show" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                        <svg x-show="show" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29" /></svg>
                                    </button>
                                </div>
                                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <label for="remember_me" class="inline-flex items-center group cursor-pointer">
                                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-[#051643] shadow-sm focus:ring-[#051643] cursor-pointer" name="remember">
                                <span class="ml-2 text-sm text-gray-600 group-hover:text-gray-900 transition-colors">{{ __('Remember me') }}</span>
                            </label>
                            
                            @if (Route::has('password.request'))
                                <a class="text-sm font-medium text-[#051643] hover:text-[#0b2b6d] transition-colors" href="{{ route('password.request') }}">
                                    Forgot password?
                                </a>
                            @endif
                        </div>

                        <button type="submit" class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-lg shadow-[#051643]/30 text-sm font-semibold text-white bg-gradient-to-r from-[#051643] to-[#0b2b6d] hover:from-[#0b2b6d] hover:to-[#051643] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#051643] transition-all duration-200 transform hover:-translate-y-0.5">
                            {{ __('Sign in') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>
