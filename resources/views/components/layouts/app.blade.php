<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{{ $title ?? 'Ürün Doğrulama' }}</title>
        
        <!-- Tailwind CSS (via CDN for simplicity, since we didn't set up Vite for frontend yet) -->
        <script src="https://cdn.tailwindcss.com"></script>
        
        <style>
            .glass {
                background: rgba(255, 255, 255, 0.7);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.3);
            }
            .animate-fade-in {
                animation: fadeIn 0.8s ease-in-out;
            }
            @keyframes fadeIn {
                0% { opacity: 0; transform: translateY(20px); }
                100% { opacity: 1; transform: translateY(0); }
            }
            body {
                background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%);
            }
        </style>
        
        @livewireStyles
    </head>
    <body class="antialiased text-gray-800 min-h-screen flex flex-col items-center justify-center p-4">
        {{ $slot }}

        @livewireScripts
    </body>
</html>
