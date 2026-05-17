<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Overenie emailu - ChromaAi') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0f172a; }
        .bg-glass { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.1); }
    </style>
</head>
<body class="antialiased text-slate-200">
    <div class="min-h-screen flex flex-col justify-center items-center px-6 py-12">
        <div class="mb-8 flex flex-col items-center text-center">
            <img src="{{ asset('logo.png') }}" alt="ChromaAi Logo" class="w-16 h-16 rounded-2xl shadow-xl shadow-purple-500/20 mb-4">
            <h1 class="text-3xl font-bold tracking-tight text-white">{{ __('Overte svoj email') }}</h1>
            <p class="mt-2 text-slate-400 max-w-sm">{{ __('Na váš email sme poslali 6-miestny overovací kód. Zadajte ho nižšie pre aktiváciu účtu.') }}</p>
        </div>

        <div class="w-full max-w-md bg-glass p-8 rounded-3xl shadow-2xl">
            @if (session('status') == 'verification-link-sent')
                <div class="mb-6 p-4 bg-green-500/10 border border-green-500/50 rounded-xl text-green-400 text-sm">
                    {{ __('Nový overovací kód bol zaslaný na vašu emailovú adresu.') }}
                </div>
            @endif

            <form method="POST" action="{{ route('verification.verify') }}" class="space-y-6">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2 text-center">{{ __('Overovací kód') }}</label>
                    <input type="text" name="code" required autofocus placeholder="123456" maxlength="6" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-4 text-center text-2xl font-bold tracking-[0.5em] text-white focus:outline-none focus:ring-2 focus:ring-purple-500/50 transition-all">
                    @error('code') <p class="mt-2 text-sm text-rose-500 text-center">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="w-full bg-purple-600 hover:bg-purple-500 text-white font-bold py-4 rounded-xl shadow-lg shadow-purple-600/20 transition-all">
                    {{ __('Overiť kód') }}
                </button>
            </form>

            <div class="mt-8 flex flex-col space-y-4">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="w-full text-sm text-slate-400 hover:text-white transition-colors underline">
                        {{ __('Neprišiel vám kód? Poslať znova') }}
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-sm text-slate-500 hover:text-rose-400 transition-colors">
                        {{ __('Odhlásiť sa') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
