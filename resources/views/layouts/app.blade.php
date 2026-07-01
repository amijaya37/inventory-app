<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Inventory Stock IT' }}</title>
    
    <!-- Premium Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-premium-dark text-slate-100 font-sans antialiased">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-[#090d16] text-white hidden md:flex md:flex-col border-r border-[#1e293b] sidebar-glow">
            <div class="h-16 flex items-center px-6 border-b border-[#1e293b]">
                <div class="flex items-center gap-2">
                    <div class="h-8 w-8 rounded-lg bg-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-600/30">
                        <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <div>
                        <div class="font-bold text-white tracking-wide text-sm">Inventory Stock IT</div>
                        <div class="text-[10px] text-indigo-400 font-semibold tracking-wider uppercase">Workspace</div>
                    </div>
                </div>
            </div>

            <nav class="flex-1 px-4 py-4 space-y-1.5 overflow-y-auto">
                @can('dashboard.view')
                    <x-sidebar-link href="{{ route('dashboard') }}" label="Dashboard" />
                @endcan

                @canany(['items.view', 'categories.view', 'suppliers.view', 'locations.view', 'users.view'])
                    <div class="px-3 pt-4 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-500">Master Data</div>
                    @can('items.view')
                        <x-sidebar-link href="{{ route('items.index') }}" label="Master Barang" />
                    @endcan
                    @can('categories.view')
                        <x-sidebar-link href="{{ route('categories.index') }}" label="Kategori" />
                    @endcan
                    @can('suppliers.view')
                        <x-sidebar-link href="{{ route('suppliers.index') }}" label="Supplier" />
                    @endcan
                    @can('locations.view')
                        <x-sidebar-link href="{{ route('locations.index') }}" label="Lokasi" />
                    @endcan
                    @can('users.view')
                        <x-sidebar-link href="{{ route('users.index') }}" label="User" />
                    @endcan
                @endcanany

                @canany(['goods-in.view', 'goods-out.view', 'returns.view', 'mutations.view'])
                    <div class="px-3 pt-4 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-500">Transaksi</div>
                    @can('goods-in.view')
                        <x-sidebar-link href="{{ route('goods-receipts.index') }}" label="Barang Masuk" />
                    @endcan
                    @can('goods-out.view')
                        <x-sidebar-link href="{{ route('goods-issues.index') }}" label="Barang Keluar" />
                    @endcan
                    @can('returns.view')
                        <x-sidebar-link href="{{ route('goods-returns.index') }}" label="Barang Tarikan" />
                    @endcan
                    @can('mutations.view')
                        <x-sidebar-link href="{{ route('stock-mutations.index') }}" label="Mutasi" />
                    @endcan
                @endcanany

                @can('stock.view')
                    <div class="px-3 pt-4 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-500">Stok</div>
                    <x-sidebar-link href="{{ route('stock.index') }}" label="Stock Gudang" />
                @endcan

                @canany(['reports.view', 'audit-log.view'])
                    <div class="px-3 pt-4 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-500">Lainnya</div>
                    @can('reports.view')
                        <x-sidebar-link href="{{ route('reports.index') }}" label="Laporan" />
                    @endcan
                    @can('audit-log.view')
                        <x-sidebar-link href="{{ route('audit-logs.index') }}" label="Audit Log" />
                    @endcan
                @endcanany
            </nav>
            
            <div class="p-4 border-t border-[#1e293b] bg-[#05070c]">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-red-400 hover:bg-red-500/10 hover:text-red-300 transition-colors">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Keluar
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Header -->
            <header class="h-16 bg-[#0e1322]/80 backdrop-blur-md border-b border-[#1e293b] flex items-center justify-between px-6 sticky top-0 z-40">
                <div>
                    <h1 class="font-semibold text-white tracking-wide">{{ $pageTitle ?? 'Dashboard' }}</h1>
                    <p class="text-[10px] text-slate-400">{{ now()->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB</p>
                </div>

                <div class="flex items-center gap-3">
                    @if(auth()->check())
                        <span class="inline-flex items-center rounded-full bg-indigo-500/10 px-2.5 py-0.5 text-xs font-semibold text-indigo-400 border border-indigo-500/20">
                            {{ auth()->user()->roles?->first()?->name ?? 'User' }}
                        </span>
                        <div class="h-4 w-[1px] bg-slate-800"></div>
                        <span class="text-sm font-semibold text-slate-300">{{ auth()->user()->name }}</span>
                    @else
                        <span class="text-sm font-semibold text-slate-300">Guest</span>
                    @endif
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 p-6 overflow-y-auto">
                @if (session('success'))
                    <div class="mb-5 rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-3.5 text-sm text-emerald-400 flex items-center gap-3">
                        <svg class="h-5 w-5 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if (session('warning'))
                    <div class="mb-5 rounded-lg border border-amber-500/30 bg-amber-500/10 px-4 py-3.5 text-sm text-amber-400 flex items-center gap-3">
                        <svg class="h-5 w-5 text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>{{ session('warning') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-5 rounded-lg border border-rose-500/30 bg-rose-500/10 px-4 py-3.5 text-sm text-rose-400 flex items-center gap-3">
                        <svg class="h-5 w-5 text-rose-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Data belum valid. Periksa kembali input Anda.</span>
                    </div>
                @endif

                {{ $slot ?? '' }}
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>

