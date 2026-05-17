<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Registrácia - ChromaAi') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0f172a; }
        .bg-glass { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.1); }
        .gradient-text { background: linear-gradient(to right, #8b5cf6, #ec4899, #f43f5e); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>
</head>
<body class="antialiased text-slate-200">
    <div class="min-h-screen flex flex-col justify-center items-center px-6 py-12">
        <div class="mb-8 flex flex-col items-center">
            <a href="/">
                <img src="{{ asset('logo.png') }}" alt="ChromaAi Logo" class="w-16 h-16 rounded-2xl shadow-xl shadow-purple-500/20 mb-4">
            </a>
            <h1 class="text-3xl font-bold tracking-tight text-white">{{ __('Vytvorte si účet') }}</h1>
        </div>

        <div class="w-full max-w-md bg-glass p-8 rounded-3xl shadow-2xl">
            <form method="POST" action="{{ route('register') }}" class="space-y-6">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">{{ __('Meno') }}</label>
                    <input type="text" name="name" value="{{ old('name') }}" required autofocus class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500/50 transition-all">
                    @error('name') <p class="mt-2 text-sm text-rose-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">{{ __('Email') }}</label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500/50 transition-all">
                    @error('email') <p class="mt-2 text-sm text-rose-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">{{ __('Heslo') }}</label>
                    <input type="password" name="password" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500/50 transition-all">
                    @error('password') <p class="mt-2 text-sm text-rose-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">{{ __('Potvrdenie hesla') }}</label>
                    <input type="password" name="password_confirmation" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500/50 transition-all">
                </div>

                <button type="submit" class="w-full bg-purple-600 hover:bg-purple-500 text-white font-bold py-4 rounded-xl shadow-lg shadow-purple-600/20 transition-all">
                    {{ __('Zaregistrovať sa') }}
                </button>
            </form>

            <div class="mt-8 text-center text-sm text-slate-500">
                {{ __('Už máte účet?') }}
                <a href="{{ route('login') }}" class="text-purple-400 hover:text-purple-300 font-semibold ml-1 transition-colors">{{ __('Prihláste sa') }}</a>
            </div>
        </div>
    </div>
</body>
</html>
