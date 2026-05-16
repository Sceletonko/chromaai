<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ChromaAi - Budúcnosť Kreativity</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        chroma: {
                            50: '#f5f3ff',
                            100: '#ede9fe',
                            200: '#ddd6fe',
                            300: '#c4b5fd',
                            400: '#a78bfa',
                            500: '#8b5cf6',
                            600: '#7c3aed',
                            700: '#6d28d9',
                            800: '#5b21b6',
                            900: '#4c1d95',
                        },
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0f172a;
        }
        .gradient-text {
            background: linear-gradient(to right, #8b5cf6, #ec4899, #f43f5e);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .bg-glass {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body class="antialiased text-slate-200 selection:bg-chroma-500 selection:text-white">
    <div class="relative min-h-screen overflow-hidden">
        <!-- Background Decorations -->
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-full -z-10">
            <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-chroma-600/20 blur-[120px] rounded-full"></div>
            <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-rose-500/10 blur-[120px] rounded-full"></div>
        </div>

        <!-- Navigation -->
        <nav class="max-w-7xl mx-auto px-6 py-8 flex justify-between items-center relative z-10">
            <div class="flex items-center space-x-2">
                <div class="w-10 h-10 bg-gradient-to-tr from-chroma-600 to-rose-500 rounded-xl flex items-center justify-center shadow-lg shadow-chroma-500/20">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <span class="text-2xl font-bold tracking-tight text-white">ChromaAi</span>
            </div>
            <div class="hidden md:flex space-x-8 text-sm font-medium text-slate-400">
                <a href="#" class="hover:text-white transition-colors">Produkty</a>
                <a href="#" class="hover:text-white transition-colors">Riešenia</a>
                <a href="#" class="hover:text-white transition-colors">Cenník</a>
            </div>
            <div>
                <a href="#" class="bg-white text-slate-900 px-5 py-2.5 rounded-full font-semibold text-sm hover:bg-slate-200 transition-all shadow-xl shadow-white/10">
                    Začať zadarmo
                </a>
            </div>
        </nav>

        <!-- Hero Section -->
        <main class="max-w-7xl mx-auto px-6 pt-20 pb-32 text-center relative z-10">
            <div class="inline-flex items-center space-x-2 bg-slate-800/50 border border-slate-700 rounded-full px-4 py-1.5 mb-8">
                <span class="flex h-2 w-2 rounded-full bg-chroma-500 animate-pulse"></span>
                <span class="text-xs font-medium text-slate-300">Nová verzia 2.0 tu</span>
            </div>

            <h1 class="text-5xl md:text-7xl font-extrabold tracking-tight mb-8">
                Posuňte svoju <br/>
                <span class="gradient-text">kreativitu s AI</span>
            </h1>

            <p class="max-w-2xl mx-auto text-lg md:text-xl text-slate-400 mb-12 leading-relaxed">
                ChromaAi vám prináša najmodernejšie nástroje generatívnej inteligencie pre vizuálnych umelcov, dizajnérov a vývojárov. Tvorte bez hraníc.
            </p>

            <div class="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-4 mb-20">
                <a href="#" class="bg-chroma-600 hover:bg-chroma-500 text-white px-8 py-4 rounded-2xl font-bold text-lg transition-all shadow-xl shadow-chroma-600/20">
                    Vyskúšať ChromaAi
                </a>
                <a href="#" class="bg-slate-800 hover:bg-slate-700 text-white px-8 py-4 rounded-2xl font-bold text-lg transition-all border border-slate-700">
                    Pozrieť demo
                </a>
            </div>

            <!-- Dashboard Preview -->
            <div class="relative max-w-5xl mx-auto">
                <div class="bg-glass rounded-3xl p-4 shadow-2xl overflow-hidden border border-white/5">
                    <div class="bg-slate-900 rounded-xl aspect-video relative flex items-center justify-center">
                        <div class="absolute top-4 left-4 flex space-x-1.5">
                            <div class="w-3 h-3 rounded-full bg-red-500/20 border border-red-500/50"></div>
                            <div class="w-3 h-3 rounded-full bg-yellow-500/20 border border-yellow-500/50"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500/20 border border-green-500/50"></div>
                        </div>
                        <div class="text-slate-500 flex flex-col items-center">
                            <svg class="w-16 h-16 mb-4 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span class="text-sm font-medium">Náhľad rozhrania ChromaAi</span>
                        </div>
                    </div>
                </div>
                <!-- Floating Elements -->
                <div class="absolute -top-6 -right-6 bg-chroma-600 p-4 rounded-2xl shadow-xl animate-bounce hidden lg:block">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="max-w-7xl mx-auto px-6 py-12 border-t border-slate-800/50 flex flex-col md:flex-row justify-between items-center text-slate-500 text-sm">
            <p>&copy; 2026 ChromaAi. Všetky práva vyhradené.</p>
            <div class="flex space-x-6 mt-4 md:mt-0">
                <a href="#" class="hover:text-white transition-colors">Ochrana údajov</a>
                <a href="#" class="hover:text-white transition-colors">Podmienky</a>
                <a href="#" class="hover:text-white transition-colors">Kontakt</a>
            </div>
        </footer>
    </div>
</body>
</html>
