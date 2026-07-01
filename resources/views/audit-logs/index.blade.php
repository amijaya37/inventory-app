@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-white tracking-wide">Audit Log</h1>
            <p class="text-xs text-slate-400">Jejak create/update/delete/post/upload/download transaksi inventory.</p>
        </div>
    </div>

    <!-- Filter Form -->
    <form method="GET" class="grid gap-5 rounded-xl border border-[#1e293b] bg-[#121826]/60 p-5 shadow-lg card-glow md:grid-cols-5">
        <div class="flex flex-col">
            <label for="module" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Module</label>
            <input id="module" name="module" value="{{ request('module') }}" placeholder="Cari module..." class="input-professional w-full">
        </div>

        <div class="flex flex-col">
            <label for="event" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Event</label>
            <input id="event" name="event" value="{{ request('event') }}" placeholder="Cari event..." class="input-professional w-full">
        </div>

        <div class="flex flex-col">
            <label for="date_from" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Dari Tanggal</label>
            <input id="date_from" type="date" name="date_from" value="{{ request('date_from') }}" class="input-professional w-full">
        </div>

        <div class="flex flex-col">
            <label for="date_to" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Sampai Tanggal</label>
            <input id="date_to" type="date" name="date_to" value="{{ request('date_to') }}" class="input-professional w-full">
        </div>

        <div class="flex items-end gap-2.5">
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-xs font-semibold text-white hover:bg-indigo-500 shadow-md shadow-indigo-600/15 transition-all cursor-pointer w-full">Filter</button>
            <a href="{{ route('audit-logs.index') }}" class="rounded-lg bg-[#090d16] border border-[#1e293b] px-4 py-2.5 text-xs font-semibold text-slate-300 hover:bg-slate-900 transition-all text-center w-full">Reset</a>
        </div>
    </form>

    <!-- Table -->
    <div class="overflow-hidden rounded-xl border border-[#1e293b] bg-[#121826]/40 shadow-xl card-glow">
        <table class="min-w-full divide-y divide-[#1e293b] text-sm">
            <thead class="bg-slate-950/60 text-left text-xs uppercase text-slate-400">
                <tr>
                    <th class="px-5 py-3.5 font-bold border-b border-[#1e293b]">Waktu</th>
                    <th class="px-5 py-3.5 font-bold border-b border-[#1e293b]">User</th>
                    <th class="px-5 py-3.5 font-bold border-b border-[#1e293b]">Event</th>
                    <th class="px-5 py-3.5 font-bold border-b border-[#1e293b]">Module</th>
                    <th class="px-5 py-3.5 font-bold border-b border-[#1e293b]">Reference</th>
                    <th class="px-5 py-3.5 font-bold border-b border-[#1e293b]">IP</th>
                    <th class="px-5 py-3.5 text-right font-bold border-b border-[#1e293b]">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#1e293b]/40">
                @forelse ($logs as $log)
                    <tr class="hover:bg-slate-900/30 transition-colors">
                        <td class="px-5 py-4 text-slate-300">{{ $log->created_at?->format('d/m/Y H:i:s') }}</td>
                        <td class="px-5 py-4 text-white font-semibold">{{ $log->user?->name ?? 'System' }}</td>
                        <td class="px-5 py-4 text-indigo-300 font-semibold">{{ $log->event }}</td>
                        <td class="px-5 py-4 text-slate-300">{{ $log->module }}</td>
                        <td class="px-5 py-4 text-slate-400 font-mono text-xs">{{ $log->reference_no ?? $log->reference_id ?? '-' }}</td>
                        <td class="px-5 py-4 text-slate-400">{{ $log->ip_address ?? '-' }}</td>
                        <td class="px-5 py-4 text-right">
                            <a class="text-indigo-400 hover:text-indigo-300 font-semibold transition-colors" href="{{ route('audit-logs.show', $log) }}">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-8 text-center text-slate-500">Belum ada log aktivitas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $logs->links() }}
    </div>
</div>
@endsection

