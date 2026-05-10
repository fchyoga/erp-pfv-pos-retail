<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS | Provit Farm Village</title>
    <!-- Use Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                        }
                    }
                }
            }
        }
    </script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        /* Custom scrollbar for better POS look */
        ::-webkit-scrollbar {
            width: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1; 
        }
        ::-webkit-scrollbar-thumb {
            background: #d1d5db; 
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #9ca3af; 
        }
    </style>
    @livewireStyles
</head>
<body class="bg-gray-100 font-sans antialiased text-gray-800 overflow-hidden">
    <div class="h-screen w-screen flex flex-col">
        <!-- Top Navbar -->
        <header class="bg-primary-600 text-white shadow-md z-10 flex items-center justify-between px-6 py-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-primary-600 font-bold text-xl">
                    PFV
                </div>
                <div>
                    <h1 class="font-bold text-lg leading-tight">Provit Farm Village</h1>
                    <p class="text-primary-100 text-xs">POS Kasir</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-right">
                    <p class="font-semibold text-sm">Super Admin</p>
                    <p class="text-primary-100 text-xs">Outlet: Hasil Peternakan</p>
                </div>
                <a href="/admin" class="bg-primary-700 hover:bg-primary-800 px-4 py-2 rounded-lg text-sm font-medium transition">
                    Dashboard Backoffice
                </a>
            </div>
        </header>

        <!-- Main Content (Livewire) -->
        <main class="flex-1 overflow-hidden relative">
            {{ $slot }}

            <!-- Toast Notification -->
            <div x-data="{ show: false, message: '' }" 
                 @notify.window="message = $event.detail[0]; show = true; setTimeout(() => show = false, 3000)"
                 x-show="show" 
                 x-transition.opacity.duration.300ms
                 class="absolute top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg font-bold flex items-center gap-2 z-50"
                 style="display: none;">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span x-text="message"></span>
            </div>
        </main>
    </div>

    @livewireScripts
</body>
</html>
