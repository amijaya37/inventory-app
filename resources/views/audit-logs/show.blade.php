@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-start justify-between">
        <div><h1 class="text-2xl font-bold">Detail Audit Log</h1><p class="text-sm text-slate-400">#{{ $auditLog->id }}</p></div>
        <a href="{{ route('audit-logs.index') }}" class="rounded border border-slate-700 px-4 py-2 text-sm">Kembali</a>
    </div>
    <div class="grid gap-4 rounded-xl border border-slate-800 bg-slate-900/70 p-5 text-sm md:grid-cols-2">
        <div><div class="text-xs text-slate-400">Waktu</div><div>{{ $auditLog->created_at?->format('d/m/Y H:i:s') }}</div></div>
        <div><div class="text-xs text-slate-400">User</div><div>{{ $auditLog->user?->name ?? 'System' }}</div></div>
        <div><div class="text-xs text-slate-400">Event</div><div>{{ $auditLog->event }}</div></div>
        <div><div class="text-xs text-slate-400">Module</div><div>{{ $auditLog->module }}</div></div>
        <div><div class="text-xs text-slate-400">Reference</div><div>{{ $auditLog->reference_no ?? $auditLog->reference_id ?? '-' }}</div></div>
        <div><div class="text-xs text-slate-400">IP</div><div>{{ $auditLog->ip_address ?? '-' }}</div></div>
        <div class="md:col-span-2"><div class="text-xs text-slate-400">User Agent</div><div class="break-all">{{ $auditLog->user_agent ?? '-' }}</div></div>
    </div>
    <div class="grid gap-4 md:grid-cols-3">
        <pre class="overflow-auto rounded-xl border border-slate-800 bg-slate-950 p-4 text-xs">{{ json_encode($auditLog->before_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        <pre class="overflow-auto rounded-xl border border-slate-800 bg-slate-950 p-4 text-xs">{{ json_encode($auditLog->after_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        <pre class="overflow-auto rounded-xl border border-slate-800 bg-slate-950 p-4 text-xs">{{ json_encode($auditLog->meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    </div>
</div>
@endsection
