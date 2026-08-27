<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Login - MikroTik Hotspot & Bandwidth Manager</title>

    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-zinc-100 dark:bg-zinc-950 text-zinc-900 dark:text-zinc-100 antialiased flex flex-col justify-center py-12 sm:px-6 lg:px-8">

    <div class="sm:mx-auto sm:w-full sm:max-w-md px-4">
        <!-- Logo & Branding -->
        <div class="flex flex-col items-center text-center">
            <div class="w-12 h-12 rounded-xl bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 flex items-center justify-center font-bold text-xl shadow-md mb-3">
                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
            <h2 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">MikroTik Manager</h2>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">Hotspot, Accounting & Bandwidth Management</p>
        </div>

        <!-- Login Card -->
        <div class="mt-8 bg-white dark:bg-zinc-900 py-8 px-6 shadow-sm border border-zinc-200 dark:border-zinc-800 rounded-2xl sm:px-10">
            @if(session('info'))
                <div class="mb-4 p-3 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 text-xs flex items-center gap-2 border border-zinc-200 dark:border-zinc-700">
                    <svg class="w-4 h-4 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('info') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 p-3 rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400 text-xs border border-rose-200 dark:border-rose-900/50">
                    <div class="font-semibold">Login Gagal</div>
                    <p class="mt-0.5">{{ $errors->first() }}</p>
                </div>
            @endif

            <form class="space-y-4" action="{{ route('login') }}" method="POST">
                @csrf
                <div>
                    <label for="email" class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Email Administrator</label>
                    <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email', 'admin@mikrotik.lan') }}" placeholder="admin@mikrotik.lan" class="w-full px-3.5 py-2.5 text-xs rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-zinc-400 dark:focus:ring-zinc-600">
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required value="password" placeholder="••••••••" class="w-full px-3.5 py-2.5 text-xs rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-zinc-400 dark:focus:ring-zinc-600">
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center gap-2 text-zinc-600 dark:text-zinc-400 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded border-zinc-300 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 focus:ring-zinc-500">
                        <span>Ingat saya</span>
                    </label>
                    <span class="text-zinc-400 text-[11px]">Default: admin@mikrotik.lan</span>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full flex justify-center py-2.5 px-4 rounded-lg shadow-xs text-xs font-semibold text-white bg-zinc-900 hover:bg-zinc-800 dark:bg-zinc-100 dark:hover:bg-zinc-200 dark:text-zinc-900 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500">
                        Masuk ke Dashboard
                    </button>
                </div>
            </form>
        </div>

        <!-- Footer / Theme Toggle -->
        <div class="mt-6 flex items-center justify-between text-xs text-zinc-400 px-2">
            <span>&copy; {{ date('Y') }} MikroTik Manager</span>
            <div class="flex items-center gap-2">
                <span>Tema:</span>
                <x-theme-toggle />
            </div>
        </div>
    </div>

</body>
</html>
