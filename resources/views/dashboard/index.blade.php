@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Row 1: KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
        <!-- KPI Card 1: Total Items -->
        <div class="relative overflow-hidden rounded-xl border border-indigo-500/20 bg-gradient-to-br from-indigo-900/40 to-slate-900/80 p-5 shadow-lg shadow-indigo-950/10 card-glow">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-indigo-300">Total Barang</span>
                <div class="rounded-lg bg-indigo-500/10 p-2 text-indigo-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <h3 class="text-3xl font-bold text-white">{{ number_format($totalItems) }}</h3>
                <p class="mt-1 text-[10px] text-indigo-400 font-semibold tracking-wide flex items-center gap-1">
                    <span class="text-emerald-400 font-bold">↑</span> Trend meningkat
                </p>
            </div>
        </div>

        <!-- KPI Card 2: Low Stock Alert -->
        <div class="relative overflow-hidden rounded-xl border border-amber-500/20 bg-gradient-to-br from-amber-950/40 to-slate-900/80 p-5 shadow-lg shadow-amber-950/10 card-glow">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-amber-300">Stok Minimum</span>
                <div class="rounded-lg bg-amber-500/10 p-2 text-amber-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <h3 class="text-3xl font-bold text-white">{{ number_format($lowStockCount) }} <span class="text-sm font-normal text-slate-400">barang</span></h3>
                <p class="mt-1 text-[10px] text-amber-400 font-semibold tracking-wide flex items-center gap-1">
                    @if($lowStockCount > 0)
                        <span class="text-amber-400 font-bold">⚠️</span> Perlu reorder segera
                    @else
                        <span class="text-emerald-400 font-bold">✓</span> Semua aman
                    @endif
                </p>
            </div>
        </div>

        <!-- KPI Card 3: Goods In Today -->
        <div class="relative overflow-hidden rounded-xl border border-emerald-500/20 bg-gradient-to-br from-emerald-950/40 to-slate-900/80 p-5 shadow-lg shadow-emerald-950/10 card-glow">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-emerald-300">Barang Masuk (Hari Ini)</span>
                <div class="rounded-lg bg-emerald-500/10 p-2 text-emerald-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 11l3-3m0 0l3 3m-3-3v8m0-13a9 9 0 110 18 9 9 0 010-18z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <h3 class="text-3xl font-bold text-white">+{{ number_format($goodsInToday) }}</h3>
                <p class="mt-1 text-[10px] text-emerald-400 font-semibold tracking-wide flex items-center gap-1">
                    Stok ter-update otomatis
                </p>
            </div>
        </div>

        <!-- KPI Card 4: Goods Out Today -->
        <div class="relative overflow-hidden rounded-xl border border-rose-500/20 bg-gradient-to-br from-rose-950/40 to-slate-900/80 p-5 shadow-lg shadow-rose-950/10 card-glow">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-rose-300">Barang Keluar (Hari Ini)</span>
                <div class="rounded-lg bg-rose-500/10 p-2 text-rose-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13l-3 3m0 0l-3-3m3 3V8m0 13a9 9 0 110-18 9 9 0 010 18z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <h3 class="text-3xl font-bold text-white">-{{ number_format($goodsOutToday) }}</h3>
                <p class="mt-1 text-[10px] text-rose-400 font-semibold tracking-wide flex items-center gap-1">
                    Distribusi alokasi IT
                </p>
            </div>
        </div>
    </div>

    <!-- Row 2: Charts and Recent Transactions -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Weekly Stock Transactions Chart -->
        <div class="lg:col-span-2 rounded-xl border border-[#1e293b] bg-[#121826]/60 p-5 card-glow flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <h2 class="font-bold text-white tracking-wide text-base">Grafik Transaksi Mingguan</h2>
                    <span class="text-[10px] text-slate-400 border border-slate-700 rounded-md px-2 py-1 bg-slate-900/40">Real-time</span>
                </div>
                <p class="text-xs text-slate-400 mt-1">Aktivitas pergerakan barang masuk dan keluar selama 7 hari terakhir.</p>
            </div>
            
            <!-- Beautiful Vector SVG Line Chart Mockup -->
            <div class="mt-6 h-48 w-full relative">
                <svg class="w-full h-full" viewBox="0 0 500 150" preserveAspectRatio="none">
                    <defs>
                        <linearGradient id="chartGradient" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#4f46e5" stop-opacity="0.3"/>
                            <stop offset="100%" stop-color="#4f46e5" stop-opacity="0"/>
                        </linearGradient>
                    </defs>
                    <!-- Background Grid -->
                    <line x1="0" y1="30" x2="500" y2="30" stroke="#1e293b" stroke-dasharray="3,3" stroke-width="0.5" />
                    <line x1="0" y1="75" x2="500" y2="75" stroke="#1e293b" stroke-dasharray="3,3" stroke-width="0.5" />
                    <line x1="0" y1="120" x2="500" y2="120" stroke="#1e293b" stroke-dasharray="3,3" stroke-width="0.5" />
                    
                    <!-- Gradient Fill Area -->
                    <path d="M 0 130 Q 80 70, 160 110 T 320 60 T 480 50 L 500 50 L 500 150 L 0 150 Z" fill="url(#chartGradient)" />
                    
                    <!-- Smooth Chart Line -->
                    <path d="M 0 130 Q 80 70, 160 110 T 320 60 T 480 50" fill="none" stroke="#6366f1" stroke-width="2.5" stroke-linecap="round" />
                    
                    <!-- Data Points Glow -->
                    <circle cx="80" cy="100" r="4" fill="#6366f1" stroke="#0e1322" stroke-width="1.5" />
                    <circle cx="240" cy="85" r="4" fill="#6366f1" stroke="#0e1322" stroke-width="1.5" />
                    <circle cx="400" cy="55" r="4" fill="#6366f1" stroke="#0e1322" stroke-width="1.5" />
                </svg>
            </div>
            
            <div class="flex justify-between items-center text-[10px] text-slate-400 mt-2 px-1">
                <span>Senin</span>
                <span>Selasa</span>
                <span>Rabu</span>
                <span>Kamis</span>
                <span>Jumat</span>
                <span>Sabtu</span>
                <span>Minggu</span>
            </div>
        </div>

        <!-- Recent Transactions Module -->
        <div class="rounded-xl border border-[#1e293b] bg-[#121826]/60 p-5 card-glow flex flex-col">
            <div class="mb-4">
                <h2 class="font-bold text-white tracking-wide text-base">Transaksi Terbaru</h2>
                <p class="text-xs text-slate-400 mt-0.5">Riwayat aktivitas gudang terakhir.</p>
            </div>

            <div class="flex-1 space-y-3 overflow-y-auto max-h-[250px] pr-1">
                @forelse($recentTransactions as $tx)
                    <div class="flex items-center justify-between p-3 rounded-lg bg-[#090d16] border border-[#1e293b]/50 hover:border-indigo-500/30 transition-all duration-200">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-semibold text-white truncate">{{ $tx['no'] }}</span>
                                <span class="text-[9px] px-2 py-0.5 rounded-full uppercase tracking-wider font-bold shrink-0
                                    @if($tx['type'] === 'Barang Masuk') bg-emerald-500/10 text-emerald-400 border border-emerald-500/20
                                    @elseif($tx['type'] === 'Barang Keluar') bg-rose-500/10 text-rose-400 border border-rose-500/20
                                    @elseif($tx['type'] === 'Mutasi') bg-indigo-500/10 text-indigo-400 border border-indigo-500/20
                                    @else bg-amber-500/10 text-amber-400 border border-amber-500/20 @endif">
                                    {{ $tx['type'] }}
                                </span>
                            </div>
                            <div class="flex items-center gap-2 mt-1 text-[10px] text-slate-400">
                                <span>{{ $tx['date']?->format('d/m/Y') ?: '-' }}</span>
                                <span>•</span>
                                <span>Qty: {{ $tx['qty'] }} items</span>
                            </div>
                        </div>
                        <div class="ml-3 shrink-0">
                            @php
                                $statusStr = is_object($tx['status']) ? $tx['status']->value : $tx['status'];
                            @endphp
                            <span class="inline-flex rounded-full px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider
                                @if($statusStr === 'posted') badge-posted
                                @elseif($statusStr === 'draft') badge-draft
                                @else badge-cancelled @endif">
                                {{ $statusStr }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="h-full flex items-center justify-center py-8 text-center text-xs text-slate-500">
                        Belum ada aktivitas transaksi.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

